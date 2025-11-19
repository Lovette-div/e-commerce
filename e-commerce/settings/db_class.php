<?php
require_once(__DIR__.'/db_cred.php');

class Database {
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->connection = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }
    }


       public function getConnection() {
        return $this->connection;
    }


    // Generic query runner
    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$result) {
            die("Query failed: " . $this->connection->error);
        }
        return $result;
    }
    
    public function executeQuery($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }
    
        if (!empty($params)) {
            $types = str_repeat("s", count($params));
            $stmt->bind_param($types, ...$params);
        }
    
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    
        return $stmt;
    }

    // Fetch multiple rows
    public function fetchAll($sql) {
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch single row
    public function fetchOne($sql) {
        $result = $this->query($sql);
        return $result->fetch_assoc();
    }

    // Escape input
    public function escapeString($value) {
        return $this->connection->real_escape_string($value);
    }

    public function getInsertId() {
        return $this->connection->insert_id;
    }

    // Close connection
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>
