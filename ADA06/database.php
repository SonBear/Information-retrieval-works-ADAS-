
<?php
/**
 * FILE TO ALL DB OPERATIONS
 */



/**
 * Init PDO for mysql server.
 */
$dsn = 'mysql:host=127.0.0.1;dbname=indexing_searching';
$pdo = new PDO(
    $dsn,
    'root', //user
    'password', //password
);

//--------------------------------------------------DOCUMENTS----------------------------------------------//
/**
 * Save document  in database
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
 * Complete information of documents for array with IDs.
 * */
function fetchDocumentsIndex($documentsDataWithID)
{
    $pdo = $GLOBALS['pdo'];
    $documents = [];
    foreach (array_keys($documentsDataWithID) as $docID) {
        $statement = $pdo->prepare("SELECT * FROM documents WHERE id=?");

        $statement->execute([$docID]);
        $response = $statement->fetchAll();

        foreach ($response as $tuple) {
            $docData = $documentsDataWithID[$docID];

            $docData['name'] = $tuple['name'];
            $docData['description'] = $tuple['description'];
            $docData['uri'] = $tuple['uri'];

            array_push($documents, $docData);
        }
    }
    return $documents;
}


//--------------------------------------------------DICTIONARY----------------------------------------------//

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

//--------------------------------------------------POSTINGS----------------------------------------------//

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
 * Get all posting from array words.
 */
function getPositionsOfWords($words)
{
    $pdo = $GLOBALS['pdo'];
    $in  = str_repeat('?,', count($words) - 1) . '?';
    $statement = $pdo->prepare("SELECT dictionary.doc_id, pos,example, word
FROM (dictionary JOIN postings ON dictionary.id = postings.dic_id) 
WHERE word IN ($in)");

    $statement->execute($words);

    $response = $statement->fetchAll();

    $documents = [];
    foreach ($response as $tuple) {
        $docID = $tuple['doc_id'];

        $doc = ['id' => $docID, 'example' => $tuple['example'], 'pos' => $tuple['pos']];


        if (isset($documents[$docID])) {
            $words = $documents[$docID];
            if (isset($words[$tuple['word']])) {
                array_push($words[$tuple['word']], $doc);
                $documents[$docID] = $words;
            } else {
                $words[$tuple['word']] = [$doc];
                $documents[$docID] = $words;
            }
        } else {
            $words = [];
            $words[$tuple['word']] = [$doc];
            $documents[$docID] = $words;
        }
    }

    return $documents;
}
//--------------------------------------------------WORDS----------------------------------------------//

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

//--------------------------------------------------VECTORS----------------------------------------------//

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

//--------------------------------------------------OPERATIONS----------------------------------------------//

/**
 * Get query for patron operation
 */
function getPatronDocuments($pattern)
{
    $query = "SELECT dictionary.doc_id, GROUP_CONCAT(example SEPARATOR '........') AS example 
FROM (dictionary JOIN postings ON dictionary.id = postings.dic_id) 
WHERE (word LIKE ? OR word LIKE ? OR word LIKE ?) GROUP BY dictionary.doc_id;";

    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare($query);
    $statement->execute(["%$pattern", "%$pattern%", "$pattern%"]);


    $response = $statement->fetchAll();

    $documents = [];
    foreach ($response as $tuple) {
        $docID = $tuple['doc_id'];
        $documents[$docID] = ['id' => $docID, 'example' => $tuple['example'], 'query' => $pattern];
    }

    return $documents;
}

/**
 * Get query for normal operation
 */
function getNormalQueryDocuments($word)
{
    $pdo = $GLOBALS['pdo'];
    $statement = $pdo->prepare("SELECT dictionary.doc_id, GROUP_CONCAT(example SEPARATOR '........') AS example 
FROM (dictionary JOIN postings ON dictionary.id = postings.dic_id) 
WHERE (word LIKE ?) GROUP BY dictionary.doc_id;");
    $statement->execute([$word]);


    $response = $statement->fetchAll();

    $documents = [];
    foreach ($response as $tuple) {
        $docID = $tuple['doc_id'];
        $documents[$docID] = ['id' => $docID, 'example' => $tuple['example'], 'query' => $word];
    }

    return $documents;
}
