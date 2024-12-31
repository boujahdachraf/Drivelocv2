<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "drive";
    public $connectpdo;

    public function __construct() {
        try {
            $this->connectpdo = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            $this->connectpdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection échouée: " . $e->getMessage();
        }
    }
    public function query($sql) {
        return $this->connectpdo->query($sql);
    }
    public function prepare($sql) {
        return $this->connectpdo->prepare($sql);
    }
}
?>