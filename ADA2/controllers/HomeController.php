<?php

    // import service
    require_once __ROOT__.'/services/SQLService.php';
    require_once __ROOT__.'/services/parser/ParserSQL.php';
    require_once __ROOT__.'/services/parser/Parser.php';

    // Instances
    $select_sql = new ParserSQL();
    $sql_service = new SQLService;
    $parser_service = new Parser();


    $results = [];
    // Get data from text field
    if(isset($_GET['fsearch'])){
        $query = $_GET['fsearch'];

        //Get table name, attributes, operators and conditions

        $data = $parser_service->parseQuery($query);

        $table_name = $data['table'];
        $attributes = $data['attributes'];
        $operators = $data['operators'];
        $conditions = $data['conditions'];



        try{
            //Create slect string sql
            $query = $select_sql -> creates_select_string($table_name, $attributes, $conditions, $operators);

            echo $query;
            //search data
            $results = $sql_service->search_sql($query);
        } catch (Exception $ex){
            echo '<script>alert("Error:'. $ex->getMessage().'")</script>';
        }       
    }

  
    // import view
    require_once __ROOT__.'/views/home.php'
?>