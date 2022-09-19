<?php 
class SQLService{
    public $servername = "localhost";
    public $username = "root";
    public $password = "admin";
    public $dbname = "northwind";
    private $conn = null;

    function __construct($servername=null, $username=null,
     $password=null, $dbname=null){
        if($servername){
            $this->servername = $servername;
        }
        if($username){
            $this->username = $username;
        }
        if($password){
            $this->password = $password;
        }
        if($dbname){
            $this->dbname = $dbname;
        }

        // Create connection
        $this->conn = $this->create_connection();
    }

    private function create_connection(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }


    function search_sql($sql_query){
        $result = $this->conn->query($sql_query);

        if ($result->num_rows > 0) {
            // output data of each row
            $results = [];
            while($row = $result->fetch_assoc()) {
                array_push($results, $row);
            }
            return $results;
        } else {
            return [];
        }
    }


}
?>