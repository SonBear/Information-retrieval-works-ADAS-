<?php

/**
 * Init PDO for mysql server.
 */
$dsn = 'mysql:host=127.0.0.1;dbname=indexing_searching';
$pdo = new PDO(
    $dsn,
    'root', //user
    'password', //password
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

    $vectorTfIdf = [];
    //calculate tf-idf vector in query
    foreach (array_keys($indexedWords) as $index) {
        array_push($vectorTfIdf, $indexedWords[$index] * $vectorIdf[$index]);
    }


    return $vectorTfIdf;
}


/**
 * Get all documents in DB
 */
function getDocuments()
{
    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare("SELECT * FROM documents");
    $statement->execute();


    $response = $statement->fetchAll();

    $documents = [];
    foreach ($response as $tuple) {
        array_push($documents, ['id' => $tuple['id'], 'name' => $tuple['name'], 'description' => $tuple['description'], 'uri' => $tuple['uri']]);
    }

    return $documents;
}

/**
 * Get vector tf-idf from one document
 */
function getVectorTfIdfForDocument($documentId)
{
    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare("SELECT TF_VECTOR.word, (idf * count) as 'tf-idf' FROM ( 
	(SELECT word, LOG10((SELECT COUNT(id) FROM documents) / COUNT(doc_id)) 
	AS idf FROM dictionary GROUP BY word) AS IDF_VECTOR
JOIN
   		(SELECT A.word, IFNULL(value, 0) as count FROM (
		(SELECT word from  dictionary
		GROUP BY word) AS A
	LEFT JOIN 
    	(SELECT word, SUM(count) AS value
		FROM dictionary WHERE doc_id = ? 
		GROUP BY word) AS B
	ON A.word = B.word) ORDER BY word) AS TF_VECTOR
ON IDF_VECTOR.word = TF_VECTOR.word
)");

    $statement->execute([$documentId]);


    $response = $statement->fetchAll();

    $vectorTfIdf = [];
    foreach ($response as $tuple) {
        array_push($vectorTfIdf, $tuple['tf-idf']);
    }

    return $vectorTfIdf;
}

/**
 * Calculates dot product from vectors
 */
function getDotProduct($vectorN1, $vectorN2)
{
    $products =
        array_map(function ($a, $b) {
            return $a * $b;
        }, $vectorN1, $vectorN2);
    return array_reduce($products, function ($a, $b) {
        return $a + $b;
    });
}

/**
 * Calculates lenght from vector
 */
function getLenght($vectorN1)
{
    $squres = array_map(function ($a) {
        return $a ** 2;
    }, $vectorN1);

    $sumSqures = array_reduce($squres, function ($a, $b) {
        return $a + $b;
    });

    return sqrt($sumSqures);
}

/**
 * Calculate score from two vectors using cosine similarity
 */
function getScoreFrom($vectorN1, $vectorN2)
{
    $dotProduct = getDotProduct($vectorN1, $vectorN2);

    $lenghtV1 = getLenght($vectorN1);
    $lenghtV2 = getLenght($vectorN2);

    if (($lenghtV1 * $lenghtV2) == 0) {
        return 0;
    }
    $score = $dotProduct / ($lenghtV1 * $lenghtV2);

    return $score;
}


/**
 * Set score attribute to documents
 */
function setDocumentsScore($documents, $vectorQuery)
{
    $documentsWithScore = [];
    foreach ($documents as $document) {
        $newDocument = $document;
        $vectorTfIdf = getVectorTfIdfForDocument($document['id']);
        $newDocument['score'] = getScoreFrom($vectorTfIdf, $vectorQuery);

        array_push($documentsWithScore, $newDocument);
    }



    return $documentsWithScore;
}

/**
 * Function to sort array on key
 */
function array_sort($array, $on, $order = SORT_DESC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

if (isset($_POST['post_document'])) {
    indexDocument();
}


$documents = [];

if (isset($_GET['query'])) {

    $vectorQuery = getVectorFromQuery($_GET['query']);
    $documents = getDocuments();

    $documents = setDocumentsScore($documents, $vectorQuery);
    $documents = array_sort($documents, 'score');
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- The data encoding type, enctype, MUST be specified as below -->
    <div class="container">
        <form enctype="multipart/form-data" action="" method="POST">
            <!-- MAX_FILE_SIZE must precede the file input field -->
            <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
            <!-- Name of input element determines name in $_FILES array -->
            Send this file: <input name="document" type="file" />
            <input type="submit" value="Send File" name="post_document" />
        </form>

        <form action="" method="GET">
            <input type="text" name="query" id="query">
            <input type="submit" value="Search" name="post_document" />
        </form>
    </div>

    <div class="container">
        <?php
        foreach ($documents as $doc) {
            echo ("<div class='row'>
            <h3>" . $doc['name'] . "</h3>
            <p>" . $doc['description'] . "</p>
            <a href='url'>" . $doc['uri'] . "</a>
            <p>" . $doc['score'] . "</p>
            <p></p>
            </div>");
        }
        ?>

    </div>


</body>

</html>