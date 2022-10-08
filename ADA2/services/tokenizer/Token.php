<?php 
    class Token{
        private $OPERATORS = ['AND', 'OR'];
        private $NO_VALUES = ['', ' '];
        public $value;
        public $name;
        public $type;

        function __construct($string){
            $this->assign_values($string);
        }


        function assign_values($string){
            $pars = [];

            if(in_array($string, $this->NO_VALUES)){
                $this->type = 'NO-VALUE';
                return;
            }

            if(in_array($string, $this->OPERATORS)){
                $this->value = $string;
                $this->name = $string;
                $this->type = 'OPERATOR';
                return;
            }
            
            $array_char = str_split($string);

            $init_par_pos = 0;
            $end_par_pos = 0;

            for($i = 0; $i < sizeof($array_char); $i++){
                if($array_char[$i] == '('){
                    array_push($pars, 1);
                    $init_par_pos = $i;
                }
                if($array_char[$i] == ')'){
                    if(sizeof($pars) == 0)
                        throw new Exception("Bad declared token function -check parentesis", 1);
                    array_pop($pars);
                    $end_par_pos = $i;
                }
            }

            $delta = $end_par_pos - $init_par_pos; 
            if(sizeof($pars) > 0){
                throw new Exception("Bad declared token function -check parentesis", 1);
            } else if ($delta > 0){
                $this->value = substr($string, $init_par_pos + 1, $delta - 1);
                $this->name = substr($string, 0, $init_par_pos);
                $this->type = 'FUNCTION';                     
            } else{
                $this->value = $string;
                $this->name = $string;
                $this->type = 'WORD';
            }
        }
    }
?>