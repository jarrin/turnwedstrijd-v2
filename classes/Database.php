<?php
/**
 * Database Class
 * Handles all database operations
 */

class Database {
    private $conn;
    
    public function __construct($host, $user, $pass, $db) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli($host, $user, $pass, $db);
            $this->conn->set_charset("utf8mb4");
        } catch (Throwable $e) {
            throw new RuntimeException('Database connection failed');
        }
    }
    
    /**
     * Get connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute query
     */
    public function query($sql) {
        $result = $this->conn->query($sql);
        if ($result === false) {
            throw new RuntimeException('Database query failed');
        }
        return $result;
    }
    
    /**
     * Prepare statement
     */
    public function prepare($sql) {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Database prepare failed');
        }
        return $stmt;
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Get affected rows
     */
    public function affectedRows() {
        return $this->conn->affected_rows;
    }
    
    /**
     * Escape string
     */
    public function escape($str) {
        return $this->conn->real_escape_string((string) $str);
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->conn->close();
    }
}
?>
