<?php
require_once __ROOT__ . '/services/parser/ConditionSQL.php';

class NormalCondition implements ConditionSQL
{

    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    public function getConditionSQL($attribute)
    {
        return $attribute . " LIKE '%$this->value%'";
    }
}
