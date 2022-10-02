<?php

    require_once __ROOT__.'/services/parser/CadenaCondition.php';
    require_once __ROOT__.'/services/parser/NormalCondition.php';
    require_once __ROOT__.'/services/parser/PatronCondition.php';


    require_once __ROOT__.'/services/tokenizer/Tokenizer.php';

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

            $tokenizer = new Tokenizer($transformed_query );
            $tokens = $tokenizer -> tokens;

            $before_type = null;
            foreach($tokens as $token){
                $type = $token -> type;

                if($type != 'OPERATOR' && $before_type != 'OPERATOR'){
                    array_push($data_frag['operators'], 'OR');
                }

                switch($type){
                    case 'FUNCTION':
                        switch($token->name){
                            case 'PATRON':
                                array_push($data_frag['conditions'], new PatronCondition(str_ireplace('-',' ', $token->value)));
                                break;
                            case 'CADENA':
                                array_push($data_frag['conditions'], new CadenaCondition(str_ireplace('-',' ', $token->value)));
                                break;
                            case 'CAMPOS':
                                $value = str_ireplace('-', '', $token->value);

                                $attributes = explode(',', $value);

                                $table = '';
                                $new_attributes = [];
                                foreach($attributes as $attr){
                                   $table_attr = explode('.', $attr);
                                   $table = $table_attr[0];
                                   array_push($new_attributes, $table_attr[1]);
                                }
                                $data_frag['table'] = $table;
                                $data_frag['attributes'] = $new_attributes;
                                break;
                        }
                        break;
                    case 'WORD':
                        array_push($data_frag['conditions'], new NormalCondition($token->value));
                        break;
                    case 'OPERATOR':
                        array_push($data_frag['operators'], $token->value);
                        break;
                }

               

                $before_type = $type;                
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

                $delta = $end_par_pos - $init_par_pos;
                if(($end_par_pos - $init_par_pos) > 0){
                    $value = substr($transformed_query , $init_par_pos, $delta + 1);
                    $new_value = str_replace(' ', '-', $value);

                    $transformed_query = substr_replace($transformed_query, $new_value, $init_par_pos, $delta + 1);

                    $init_par_pos = 0;
                    $end_par_pos = 0;
                }
            }
            return $transformed_query;
        }
    }

?>