<?php
require_once PROJECT_ROOT_PATH . "/model/Database.php";

class DocumentModel extends Database
{
    public function getDocuments()
    {
        return $this->select("SELECT * FROM documents");
    }
}
