<?php
require_once __ROOT__ . '/services/SQLService.php';

abstract class PersistenceService
{
    public $SQLService;

    public function __construct()
    {
        $this->SQLService = new SQLService();
    }

    public abstract function save($data);
    public abstract function search($query);
    public abstract function delete($id);
    public abstract function update($id, $data);
}
