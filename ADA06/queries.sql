
### calculate IDR vector
# Get number of times that term is in documents
SELECT word, LOG10((SELECT COUNT(id) FROM documents) / COUNT(doc_id)) 
	AS idf FROM dictionary GROUP BY word;
    
### CALCULATE TF vector for document
# First Generate vector get all vocabulary available
# Following get every occurence of words in each document.
# And Join tables
SELECT A.word, IFNULL(value, 0) as count FROM (
	(SELECT word from  dictionary
	GROUP BY word) AS A
LEFT JOIN 
    (SELECT word, SUM(count) AS value
	FROM dictionary WHERE doc_id = 5 
	GROUP BY word) AS B
ON A.word = B.word) ORDER BY word;

### Testing calculate vector tf-idr of one document
SELECT TF_VECTOR.word, (idf * count) as 'tf-idf' FROM ( 
	(SELECT word, LOG10((SELECT COUNT(id) FROM documents) / COUNT(doc_id)) 
	AS idf FROM dictionary GROUP BY word) AS IDF_VECTOR
JOIN
   		(SELECT A.word, IFNULL(value, 0) as count FROM (
		(SELECT word from  dictionary
		GROUP BY word) AS A
	LEFT JOIN 
    	(SELECT word, SUM(count) AS value
		FROM dictionary WHERE doc_id = 45 
		GROUP BY word) AS B
	ON A.word = B.word) ORDER BY word) AS TF_VECTOR
ON IDF_VECTOR.word = TF_VECTOR.word
);


## Get documents from query without CAMPOS
SELECT dictionary.doc_id, GROUP_CONCAT(example SEPARATOR '........') AS example 
FROM (dictionary JOIN postings ON dictionary.id = postings.dic_id) 
WHERE (word LIKE '%se' OR word LIKE '%se%' OR word LIKE 'se%') GROUP BY dictionary.doc_id;

## Get documents from query with CAMPOS
SELECT dictionary.doc_id, pos,example, word
FROM (dictionary JOIN postings ON dictionary.id = postings.dic_id) 
WHERE word IN ('ha', 'sido');

## Get documents from query with CAMPOS
SELECT dictionary.doc_id, pos,example
FROM (dictionary JOIN postings ON dictionary.id = postings.dic_id) 
WHERE (word ='sido');

## Get documents from query with CAMPOS
SELECT dictionary.doc_id, word, pos, example 
FROM (dictionary JOIN postings ON dictionary.id = postings.dic_id)
WHERE (word = 'como');

SELECT word from dictionary GROUP BY word ORDER BY word;


DELETE FROM postings;

DELETE FROM dictionary;

DELETE FROM documents;
