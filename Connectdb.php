<?php

class Database
{
    private $host = "localhost";
    private $dbName = "Driveloc";
    private $username = "root";
    private $password = "";
    private $Connectpdo;

    public function __construct()
    {
        // Try to connect to the database
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName}";
            $this->pdo = new PDO($dsn, $this->username, $this->password);

            // Best practice because of it help catching errrors
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }
}