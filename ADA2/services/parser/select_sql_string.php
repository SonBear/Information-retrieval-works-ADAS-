<?php
    class SelectSQLString{

        function creates_select_string($table, $attributes, $conditions, $operators){
            $string_sql = 'SELECT * FROM '.$table.' WHERE ';

                $current_op = 0;
                $n_attr = sizeof($attributes);
                $n_cond = sizeof($conditions);
                foreach($conditions as $con){
                    
                    for($j = 0; $j < $n_attr; $j++){
                        $string_sql = $string_sql.($con->getConditionSQL($attributes[$j]));
                        if($j < $n_attr - 1)
                            $string_sql = $string_sql.' OR '; 
                    }
                    
                   
                    if($current_op < $n_cond - 1){
                        $string_sql = $string_sql.' '.$operators[$current_op++].' ';
                    }
                }
                
            

            return $string_sql;
        }
    }

?>