<?php
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
            
            echo $transformed_query;
            echo '</br>';

            $tokens = explode(" ", $transformed_query);

            $before_token = null;
            foreach($tokens as $token){
                $current_token = strtolower($token);

                echo $token;
                echo '</br>';
                if($before_token != null && in_array($OPERATORS, $current_token)){
                    $ops = $data_frag -> operators;
                    array_push($ops, $token);
                    $data_frag['operators'] = $ops;
                }
            }

            var_dump($data_frag);
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