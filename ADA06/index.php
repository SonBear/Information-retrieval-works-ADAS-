<?php


require_once __DIR__ . '\database.php';
require_once __DIR__ . '\math.php';
require_once __DIR__ . '\util.php';
require_once __DIR__ . '\indexer.php';

/**
 * Define upload documents directory.
 */
define('_DIRDOCS_', __DIR__ . '\uploads\docs\\');


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
 * Get all documents that have all words in string.
 */
function getCadenaQueryDocuments($param)
{
    $words = preg_split('/[\s]+/', $param, -1, PREG_SPLIT_NO_EMPTY);
    $nWords = sizeof($words);

    if ($nWords == 0) {
        return [];
    }
    if ($nWords == 1) {
        return getNormalQueryDocuments($param);
    }

    $documentsMatch = getPositionsOfWords($words);
    $documents = [];
    $checkOtherWord = true;
    foreach (array_keys($documentsMatch) as $docID) {

        for ($i = 0; $i < ($nWords - 1); $i++) {
            if (!$checkOtherWord && $i != 0)
                break;

            $checkOtherWord = false;
            $wordsIndex = $documentsMatch[$docID];

            if (!isset($wordsIndex[$words[$i]])) {
                break;
            }
            if (!isset($wordsIndex[$words[$i + 1]])) {
                break;
            }
            $posCurrentDocument = $wordsIndex[$words[$i]];
            $posNextDocument = $wordsIndex[$words[$i + 1]];

            foreach ($posCurrentDocument as $currentPos) {
                foreach ($posNextDocument as $nextPos) {

                    $posC = $currentPos['pos'];
                    $posN = $nextPos['pos'];


                    if (abs($posN - $posC) === 1) {
                        $checkOtherWord = true;
                        array_push($documents, $currentPos);
                        array_push($documents, $nextPos);
                    }
                }
            }
        }
    }

    $documentsIndex = [];
    foreach ($documents as $document) {
        $docID = $document['id'];

        if (isset($documentsIndex[$docID])) {
            $doc = $documentsIndex[$docID];
            $doc['example'] = $doc['example'] . '........' . $document['example'];
            $documentsIndex[$docID] = $doc;
        } else {
            $documentsIndex[$docID] = ['id' => $docID, 'example' => $document['example'], 'query' => $param];
        }
    }

    return $documentsIndex;
}


/** 
 * Process operators in query 
 * */
function processDocumentsWithOperators($documentsResults, $operators)
{
    $nResults = sizeof($documentsResults);
    if ($nResults == 0) {
        return [];
    }
    if ($nResults == 1) {
        return $documentsResults[0];
    }


    $currentIndex = 0;
    $currentDocuments = $documentsResults[$currentIndex];
    foreach ($operators as $op) {
        if ($op == 'AND') {
            $currentDocuments = array_intersect($currentDocuments, $documentsResults[$currentIndex + 1]);
        } else {
            $currentDocuments = array_unique(array_merge($currentDocuments, $documentsResults[$currentIndex + 1]));
        }

        $currentIndex += 1;
    }

    return $currentDocuments;
}

/**
 * Get function query name.
 */
function extfn_name($input)
{
    $regex = "/\(.*?\)/";
    return preg_replace($regex, "", $input);
}

/**
 * Get params of function in query.
 */
function extparams($input)
{
    $input = str_replace("'", "", $input);
    $matches = array();
    $regex = "#\((([^()]+|(?R))*)\)#";
    if (preg_match($regex, $input, $matches)) {
        return $matches[1];
    } else {
        return $input;
    }
}
/**
 * Process query to get documents.
 */
function getDocumentsFromQuery($query)
{
    $tokens = array();
    preg_match_all('/\w+\(.*?\)|\w+/', $query, $tokens);

    $operators = [];
    $isOperatorLastToken = false;
    $index = 0;
    $documentsIds = [];
    $documentsIndexs = [];
    foreach ($tokens[0] as $token) {
        $name = extfn_name($token);
        $params = extparams($token);
        switch ($name) {
            case 'PATRON':
                if (!$isOperatorLastToken && $index != 0)
                    array_push($operators, 'OR');

                $patronDocumentsIndex = getPatronDocuments($params);

                array_push($documentsIds, array_keys($patronDocumentsIndex));
                array_push($documentsIndexs, $patronDocumentsIndex);

                $isOperatorLastToken = false;
                break;
            case 'CADENA':
                if (!$isOperatorLastToken && $index != 0)
                    array_push($operators, 'OR');

                $cadenaDocumentsIndex = getCadenaQueryDocuments($params);

                array_push($documentsIds, array_keys($cadenaDocumentsIndex));
                array_push($documentsIndexs, $cadenaDocumentsIndex);

                $isOperatorLastToken = false;
                break;
            case in_array($name, ['AND', 'OR']):
                if ($isOperatorLastToken)
                    throw new Exception('No se puede tener dos peradores juntos en la query');

                array_push($operators, $name);

                $isOperatorLastToken = true;
                break;
            default:
                if (!$isOperatorLastToken && $index != 0)
                    array_push($operators, 'OR');

                $normalDocumentsIndex = getNormalQueryDocuments($name);

                array_push($documentsIds, array_keys($normalDocumentsIndex));
                array_push($documentsIndexs, $normalDocumentsIndex);

                $isOperatorLastToken = false;
                break;
        }
        $index += 1;
    }

    $docsIdFiltered = processDocumentsWithOperators($documentsIds, $operators);


    $resultIndexDocs = [];
    foreach ($docsIdFiltered as $docID) {
        foreach ($documentsIndexs as $docIndex) {
            if (isset($docIndex[$docID]) && !isset($resultIndexDocs[$docID]))
                $resultIndexDocs[$docID] = $docIndex[$docID];
        }
    }

    $finalDataDocuments = fetchDocumentsIndex($resultIndexDocs);
    return $finalDataDocuments;
}


/**
 * Print description marked querys words.
 */
function printDescription($document)
{
    $description = $document['example'];
    $query = $document['query'];

    $description = str_ireplace($query, "<b>$query</b>", $description);

    return $description;
}

/** Control form to upload document */
if (isset($_POST['post_document'])) {
    indexDocument();
}

/** Control input search */
$documents = [];
if (isset($_GET['query'])) {

    $vectorQuery = getVectorFromQuery($_GET['query']);
    $documents = getDocumentsFromQuery($_GET['query']);
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
            <i>" . printDescription($doc) . " </i>
            <br>
            <a href=" . $doc['uri'] . ">" . $doc['uri'] . "</a>
            <p>" . $doc['score'] . "</p>
            <p></p>
            </div>");
        }
        ?>

    </div>


</body>

</html>