<?php
require_once __ROOT__ . '/services/tokenizer/Token.php';
class Tokenizer
{

    public $tokens_size = 0;
    public $tokens = [];

    function __construct($string)
    {
        $this->tokenizer_string(' ', $string);
    }

    public function tokenizer_string($char, $string)
    {

        $raw_tokens = explode($char, $string);

        $before_type = null;
        $count_campos = 0;
        foreach ($raw_tokens as $raw_token) {
            $token = new Token($raw_token);
            $token_type = $token->type;

            array_push($this->tokens, $token);
            $this->tokens_size += 1;

            if ($before_type == 'OPERATOR' && $before_type == $token->type) {
                throw new Exception('BAD STRUCT STRING QUERY', 1);
            }
            if ($token_type == 'FUNCTION') {
                if ($token->name == 'CAMPOS') {
                    $count_campos += 1;
                }
            }
            $before_type = $token->type;
        }

        $tokens = $this->tokens;
        $last_index = $this->tokens_size - 1;
        $last_token = $tokens[$last_index];
        $last_name = $last_token->name;

        if ($count_campos > 1) {
            throw new Exception('BAD STRUCT STRING QUERY - ONLY CAN HAVE ONE CAMPOS FUNCTION', 1);
        }
        if ($this->tokens_size > 0 && $count_campos > 0 && $last_name != 'CAMPOS') {
            throw new Exception('BAD STRUCT STRING QUERY - CAMPOS MUST BE IN END OF QUERY', 1);
        }
    }
}
