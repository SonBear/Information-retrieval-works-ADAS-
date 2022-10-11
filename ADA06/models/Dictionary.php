<?php
class Dictionary
{
    public $id;
    public $doc_id;
    public $word;
    public $count;

    function __construct($id, $doc_id, $word, $count = null)
    {
        $this->id = $id;
        $this->doc_id = $doc_id;
        $this->word = $word;
        $this->count = $count;
        if ($count == null) {
            $this->count = 0;
        }
    }
}
