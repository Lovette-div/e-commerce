<?php
include_once 'db_cred.php';

class Database {
    protected $connection;

    public function __construct() {
        $this->connect();
    }

    // Connect to DB
    private function connect() {
        $this->connection = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }
    }

    // Execute query with prepared statements
    public function executeQuery($query, $params = []) {
        $stmt = $this->connection->prepare($query);

        if ($stmt === false) {
            throw new Exception("SQL error: " . $this->connection->error);
        }

        if (!empty($params)) {
            // dynamically bind params
            $types = str_repeat("s", count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>
