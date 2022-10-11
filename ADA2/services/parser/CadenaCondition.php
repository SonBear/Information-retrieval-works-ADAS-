<?php
require_once __ROOT__ . '/services/parser/ConditionSQL.php';

class CadenaCondition implements ConditionSQL
{

    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    public function getConditionSQL($attribute)
    {
        return $attribute . " = '$this->value'";
    }
}
