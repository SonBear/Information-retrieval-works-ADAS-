<?php
class Document
{
    public $id;
    public $name;
    public $description;
    public $uri;

    function __construct($id, $uri, $name, $description = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->uri = $uri;
        $this->description = $description;
        if ($description == null) {
            $this->description = '';
        }
    }
}
