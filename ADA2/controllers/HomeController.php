<?php

// import service
require_once __ROOT__ . '/services/SQLService.php';
require_once __ROOT__ . '/services/parser/ParserSQL.php';
require_once __ROOT__ . '/services/parser/Parser.php';

// Instances
$parser_sql = new ParserSQL();
$sql_service = new SQLService;
$parser_service = new Parser();


$results = [];
// Get data from text field
if (isset($_GET['fsearch'])) {
    $query = $_GET['fsearch'];

    if ($query != '') {
        try {
            //Get table name, attributes, operators and conditions

            $data = $parser_service->parseQuery($query);

            $table_name = $data['table'];
            $attributes = $data['attributes'];
            $operators = $data['operators'];
            $conditions = $data['conditions'];

            //Create slect string sql
            $query = $parser_sql->creates_select_string($table_name, $attributes, $conditions, $operators);
            //search data
            $results = $sql_service->search_sql($query);
        } catch (Exception $ex) {
            echo '<script>alert("Error:' . $ex->getMessage() . '")</script>';
        }
    }
}


// import view
require_once __ROOT__ . '/views/home.php';
