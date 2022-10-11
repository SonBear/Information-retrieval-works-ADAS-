<?php
class Document
{
    public $id;
    public $name;
    public $desciption;
    public $uri;

    function __construct($id, $uri, $name, $desciption = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->uri = $uri;
        $this->desciption = $desciption;
        if ($desciption == null) {
            $this->desciption = '';
        }
    }
}
