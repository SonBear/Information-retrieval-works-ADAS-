<?php
require_once __ROOT__ . '/services/PersistenceService.php';

class DictionaryService extends PersistenceService
{
    public function save($data)
    {
        $newData = $data;
        $conn = $this->SQLService->conn;
        $sql = "INSERT INTO dictionary (id, doc_id, word, count) VALUES (NULL, '$data->doc_id', '$data->word', '$data->count')";
        $res = $conn->query($sql);
        if ($res === TRUE) {
            $newData->id = $conn->insert_id;
            return $newData;
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    public function search($query)
    {
    }
    public function delete($id)
    {
    }
    public function update($id, $data)
    {
    }
}
