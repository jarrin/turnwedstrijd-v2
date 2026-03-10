<?php
/**
 * Apparatus Class
 * Handles apparatus/equipment operations
 */

class Apparatus {
    private $db;
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    /**
     * Get all apparatus
     */
    public function getAll() {
        $sql = "SELECT id, naam AS name, geslacht
            FROM onderdeel
            ORDER BY geslacht ASC, id ASC";
        $result = $this->db->query($sql);
        $apparatus = [];
        
        while ($row = $result->fetch_assoc()) {
            $apparatus[] = $row;
        }
        
        return $apparatus;
    }
    
    /**
     * Get apparatus by ID
     */
    public function getById($id) {
        $sql = "SELECT id, naam AS name, geslacht FROM onderdeel WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
