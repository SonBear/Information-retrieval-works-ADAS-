<?php

/**
 * Init PDO for mysql server.
 */
$dsn = 'mysql:host=127.0.0.1;dbname=indexing_searching';
$pdo = new PDO(
    $dsn,
    'root',
    'password',
);


/**
 * Define upload documents directory.
 */
define('_DIRDOCS_', __DIR__ . '\uploads\docs\\');

/**
 * Save document in server.
 */
function saveDocument()
{
    if (($_FILES['document']['name'] != "")) {
        $file = $_FILES['document']['name'];
        $path = pathinfo($file);
        $filename = $path['filename'];
        $ext = $path['extension'];
        $temp_name = $_FILES['document']['tmp_name'];

        $path_filename_ext = _DIRDOCS_ . uniqid(rand(), false)  . "." . $ext;
        move_uploaded_file($temp_name, $path_filename_ext);
        echo "Congratulations! File Uploaded Successfully.";

        $document = saveDocumentDB($filename, $path_filename_ext);
        return $document;
    }
    return [];
}

/**
 * Save document tuple in database
 */
function saveDocumentDB($name, $uri, $description = "Documento nuevo")
{
    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare("INSERT INTO documents (id, name, description, uri) VALUES (?, ?, ?, ?)");
    $statement->execute([null, $name, $description, $uri]);

    $statement->closeCursor();

    $document_id = $pdo->lastInsertId();
    return ['id' => $document_id, 'name' => $name, 'description' => $description, 'uri' => $uri];
}

/**
 * Get inverse index of file
 */
function getInvertedIndex($fileDir)
{
    $filecontents = file_get_contents($fileDir);

    $originalWords = preg_split('/[\s]+/', $filecontents, -1, PREG_SPLIT_NO_EMPTY);
    $contentWithoutPunctuation = str_replace(['?', '!', '.', ',', '(', ')'], '', $filecontents);
    $words = preg_split('/[\s]+/', $contentWithoutPunctuation, -1, PREG_SPLIT_NO_EMPTY);

    $inversedIndex = [];

    $n_words = sizeof($words);
    for ($pos = 1; $pos <= $n_words; $pos++) {
        $wordKey = strtolower($words[$pos - 1]);

        $init = abs((($pos - 6) % $n_words));
        $example = implode(" ", array_slice($originalWords, $init, 12));
        $posting = [
            'pos' => $pos,
            'example' => $example
        ];

        if (isset($inversedIndex[$wordKey])) {
            $inversedIndex[$wordKey]['count'] += 1;
            array_push($inversedIndex[$wordKey]['postings'], $posting);
        } else {
            $inversedIndex[$wordKey] = ['count' => 1, 'postings' => [$posting]];
        }
    }

    return $inversedIndex;
}

/**
 * Save dictionary tuple in database.
 */
function saveDictionary($document, $wordKey, $count)
{
    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare("INSERT INTO dictionary (id, doc_id, word, count) VALUES (?, ?, ?, ?)");
    $statement->execute([null, $document['id'], $wordKey, $count]);

    $statement->closeCursor();

    $dictionary_id = $pdo->lastInsertId();
    return ['id' => $dictionary_id, 'doc_id' => $document['id'], 'count' => $count];
}

/**
 * Save posting tuple in database.
 */
function savePostings($document, $dictionary, $pos, $example)
{
    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare("INSERT INTO postings (id, doc_id, dic_id, pos, example) VALUES (?, ?, ?, ?, ?)");
    $statement->execute([null, $document['id'], $dictionary['id'], $pos, $example]);

    $statement->closeCursor();

    $posting_id = $pdo->lastInsertId();
    return ['$id' => $posting_id, 'doc_id' => $document['id'], 'dic_id' => $dictionary['id'], 'pos' => $pos, 'example' => $example];
}


/**
 * Index all document
 */
function indexDocument()
{
    $document = saveDocument();
    $inversedIndex = getInvertedIndex($document['uri']);

    $indexs = array_keys($inversedIndex);
    foreach ($indexs as $index) {

        $dictionary = saveDictionary($document, $index, $inversedIndex[$index]['count']);
        $postings = $inversedIndex[$index]['postings'];
        foreach ($postings as $posting) {
            savePostings($document, $dictionary, $posting['pos'], $posting['example']);
        }
    }
    echo var_dump('documento indexado');
}


/**
 * Get all words from database in inverse index.
 */
function getIndexedWords()
{
    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare("SELECT word from dictionary GROUP BY word ORDER BY word");
    $statement->execute();


    $words = $statement->fetchAll();

    $indexedWords = [];

    foreach ($words as $word) {
        $indexedWords[$word['word']] = 0;
    }
    return $indexedWords;
}

/**
 * Obtain idf vector from database.
 */
function getIdfVector()
{
    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare("SELECT word, LOG10((SELECT COUNT(id) FROM documents) / COUNT(doc_id)) 
	AS idf FROM dictionary GROUP BY word;");
    $statement->execute();


    $response = $statement->fetchAll();

    $vectorIdf = [];

    foreach ($response as $row) {
        $vectorIdf[$row['word']] = $row['idf'];
    }
    return $vectorIdf;
}

/**
 * Transform query into vector tf-idf.
 */
function getVectorFromQuery($query)
{

    //Filter query
    //Deletes Operators
    //Deletes Parentesis
    //Deletes functions
    $query = str_replace(["AND", "OR", "CADENA", "(", ")", "PATRON", "'"], "", $query);

    $vectorIdf =  getIdfVector();
    $indexedWords = getIndexedWords();

    //count tf in query
    $wordsQuery = explode(" ", $query);
    foreach ($wordsQuery as $word) {
        if (isset($indexedWords[$word]))
            $indexedWords[$word] += 1;
    }

    //calculate tf-idf vector in query
    foreach (array_keys($indexedWords) as $index) {
        $indexedWords[$index] *= $vectorIdf[$index];
    }


    return $indexedWords;
}


if (isset($_POST['post_document'])) {
    indexDocument();
}


$vectorQuery = '';

if (isset($_GET['query'])) {

    $vectorQuery = getVectorFromQuery($_GET['query']);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <!-- The data encoding type, enctype, MUST be specified as below -->
    <form enctype="multipart/form-data" action="" method="POST">
        <!-- MAX_FILE_SIZE must precede the file input field -->
        <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
        <!-- Name of input element determines name in $_FILES array -->
        Send this file: <input name="document" type="file" />
        <input type="submit" value="Send File" name="post_document" />
    </form>

    <form action="" method="GET">
        <input type="text" name="query" id="query">
        <input type="submit" value="Send File" name="post_document" />
    </form>

    <?php
    echo "<p>" . var_dump($vectorQuery) . "</p>"
    ?>

</body>

</html>