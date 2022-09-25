<?php

    // import service
    require_once __ROOT__.'/services/sql_service.php';
    require_once __ROOT__.'/services/parser/select_sql_string.php';

    require_once __ROOT__.'/services/parser/CadenaCondition.php';
    require_once __ROOT__.'/services/parser/PatronCondition.php';
    require_once __ROOT__.'/services/parser/NormalCondition.php';

    $select_sql = new SelectSQLString();
    $sql_service = new SQLService;

    $results = [];
    // Get data from text field
    if(isset($_GET['fsearch'])){
        $query = $_GET['fsearch'];

        //Check valid query
        if($query != 'good'){
            throw new Exception();
        }

        //Get table name, attributes, operators and conditions
        $table_name = '';
        $attributes = [];
        $operator = [];
        $conditions = [];



        //Create slect string sql
        $query = $select_sql -> creates_select_string('products', ['product_name', 'category'], [new CadenaCondition('cereal'), new PatronCondition('cer')], ["AND"]);
        echo $query;
        //search data
        $results = $sql_service->search_sql($query);
       
    }

  
    // import view
    require_once __ROOT__.'/views/home.php'
?>