<?php

    // import service
    require_once __ROOT__.'/services/sql_service.php';
    $sql_service = new SQLService;

    $results = [];
    // Get data from text field
    if(isset($_GET['fsearch'])){
        $query = $_GET['fsearch'];

        //Export data to quey sql

        //search data
        $results = $sql_service->search_sql($query);
        echo sizeof($results);
    }

  
    // import view
    require_once __ROOT__.'/views/home.php'
?>