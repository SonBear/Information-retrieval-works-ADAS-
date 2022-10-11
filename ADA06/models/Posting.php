<?php
class Posting
{
    public $id;
    public $doc_id;
    public $dic_id;
    public $pos;

    function __construct($id, $doc_id, $dic_id, $pos)
    {
        $this->id = $id;
        $this->doc_id = $doc_id;
        $this->dic_id = $dic_id;
        $this->pos = $pos;
    }
}
