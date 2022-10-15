
### calculate IDR vector
# Get number of times that term is in documents
SELECT word, LOG10(COUNT(doc_id) / (SELECT COUNT(id) FROM documents)) 
	AS idr FROM dictionary GROUP BY word;
    
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

### Testing calculate vector tf-idr
SELECT TF_VECTOR.word, (idr * count) as 'tf-idr' FROM ( 
	(SELECT word, LOG10(COUNT(doc_id) / (SELECT COUNT(id) FROM documents)) 
	AS idr FROM dictionary GROUP BY word) AS IDR_VECTOR
JOIN
   		(SELECT A.word, IFNULL(value, 0) as count FROM (
		(SELECT word from  dictionary
		GROUP BY word) AS A
	LEFT JOIN 
    	(SELECT word, SUM(count) AS value
		FROM dictionary WHERE doc_id = 5 
		GROUP BY word) AS B
	ON A.word = B.word) ORDER BY word) AS TF_VECTOR
ON IDR_VECTOR.word = TF_VECTOR.word
);


## Get documents from query without CAMPOS
## agregar unir cada examplo en una cadena
SELECT dictionary.doc_id, word, pos, example FROM (dictionary JOIN postings ON dictionary.id = postings.dic_id) WHERE (word = 'adios' OR word = 'Jugar' OR word='hola');


## Get documents from query without CAMPOS









