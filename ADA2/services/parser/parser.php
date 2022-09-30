<?php

    require_once __ROOT__.'/services/parser/CadenaCondition.php';

    private class Token{
        private $data = '';
        private $raw_token = '';


        function __construct($raw_token){
            $this -> data = $this->getDataFromToken($raw_token);
            $this -> raw_token = $raw_token;
        }

        private function getDataFromToken($token){
            /**
             * Logic insert
             */

            return $token;
        }

        public function checkToken($data_frag){
            $current_token = $this->raw_token;
            $new_data_frag = $data_frag;

            if(substr_compare($current_token, 'cadena'))){
                $data = getDataBetweenPar($current_token);
                array_push($new_data_frag['conditions'], new CadenaCondition($this->data));

            } else if(substr_compare($current_token, 'patron'))){
                $data = getDataBetweenPar($current_token);
                array_push($new_data_frag['conditions'] new CadenaCondition($data));

            }

            else if(in_array($OPERATORS, $current_token)){
                $ops = $data_frag -> operators;
                array_push($ops, $token);
                $data_frag['operators'] = $ops;
            }

            return $data_frag;
        }
    }
    class ParserCL{
        private $TABLE_DAFAULT = 'products';
        private $ATTRIBUTES_DEFAULT = ['product_name', 'name', 'category'];

        private $OPERATORS = ['and', 'or'];
        private $FUNCTIONS = ['cadena', 'patron'];

        public function parseQuery($query){
            $data_frag = [
                'table' => $this->TABLE_DAFAULT, 
                'attributes' => $this->ATTRIBUTES_DEFAULT,
                'operators' => [],
                'conditions' => []
            ];

            $transformed_query = $this->setFunctionValuesToASCII($query);
            $tokens = explode(" ", $transformed_query);

            $before_token = null;
            foreach($tokens as $token){
                $current_token = strtolower($token);

                $conds = $data_frag['conditions'];
                if(substr_compare($current_token, 'cadena'))){
                    $data = getDataBetweenPar($current_token);
                    array_push($conds, new CadenaCondition($data));

                } else if(substr_compare($current_token, 'patron'))){
                    $data = getDataBetweenPar($current_token);
                    array_push($conds, new CadenaCondition($data));

                }

                if($before_token != null && in_array($OPERATORS, $current_token)){
                    $ops = $data_frag -> operators;
                    array_push($ops, $token);
                    $data_frag['operators'] = $ops;
                }

                $before_token = $current_token;
            }
            return $data_frag;
        }    

        private function setFunctionValuesToASCII($query){
            $array_char = str_split($query);

            $init_par_pos = 0;
            $end_par_pos = 0;

            $transformed_query = $query;
            for($i = 0; $i < sizeof($array_char); $i++){
                if($array_char[$i] == '('){
                    $init_par_pos = $i;
                }
                if($array_char[$i] == ')'){
                    $end_par_pos = $i;
                }

                if(($end_par_pos - $init_par_pos) > 0){
                    $value = substr($transformed_query, $init_par_pos, $end_par_pos);

                    $new_value = str_replace(' ', '-', $value);
                    $transformed_query = substr_replace($transformed_query, $new_value, $init_par_pos, $end_par_pos);

                    $init_par_pos = 0;
                    $end_par_pos = 0;

                    echo $transformed_query;
                    echo '</br>';
                }
            }
            return $transformed_query;
        }
    }

?>