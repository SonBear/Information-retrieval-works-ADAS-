<?php

/**
 * FILE WITH MATH OPERATIONS
 */

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
