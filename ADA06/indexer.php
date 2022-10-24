<?php

/**
 * FILE FOR INDEXING OPERATIONS
 */

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

        $document = saveDocumentDB($filename, $path_filename_ext);
        return $document;
    }
    return [];
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
