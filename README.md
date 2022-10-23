# Information-retrieval-works-ADAS-

All activities for Information Retrieval subject of UADY university.

# ADA01

# ADA02

# ADA06

Indexación y busqueda de archivos de texto.

## Como ejecutar la app

1. Crear un directorio uploads\docs dentro del directorio de ADA06 (No he probado si el directorio se crea solo...).
2. Servir la aplicación (xaamp por ejemplo)
3. Ubicarse en la ruta que lleve al directorio ADA06 (por ejemplo: localhos/ada06/index.php)

## Generar estructura de la base de datos

1. Ubicar el archivo ADA06/indexing_searching.sql
2. Crear una base de datos de nombre "indexing_searching" en tu servidor de base de datos(mysql)
3. Ejecutar los comandos del archivo indexing_searching.sql en la base de datos creada.
4. En el archivo ADA06/index.php verifica que el usuario y contraseña de tu base de datos sea la correcta (lineas 9 y 10);

## Como subir un archivo

1. Con el botón choose file selecciona un archivo de texto (Se puede usar los archivos de ejemplo en el directorio "ejemplos")
2. Presiona send file (el archivo se subirá al directorio ADA06/uploads/docs, de igual manera se indexará y registrará en la base de datos)

## Como usar la busqueda

1. En el campo de texto escribe alguna cadena que desees
2. Posteriormente presiona el botón de "search" (La cadena se procesará para formar un vector tf-idf, después comparará ese vector aplicando la similitud de cosenos con todos los vectores tf-idf de los documentos)
3. Se presentarán todos los documentos ordenados por su atributo score (resultado de la similitud de cosenos).
