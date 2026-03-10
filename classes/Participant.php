<?php
/**
 * Participant Class
 * Handles participant operations
 */

class Participant {
    private $db;

    private const DEFAULT_WEDSTRIJD_ID = 1;
    private const NAME_MAX_LENGTH = 120;
    private const GROUP_MAX_LENGTH = 100;
    private const ALLOWED_GENDERS = ['Heren', 'Dames'];
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    /**
     * Get all participants
     */
    public function getAll() {
        $sql = "SELECT 
                    d.id,
                    d.naam AS name,
                    d.lidnummer AS number,
                    d.geslacht,
                    g.naam AS group_name,
                    g.id AS group_id
                FROM deelnemer d
                LEFT JOIN groep g ON d.groep_id = g.id
                ORDER BY d.lidnummer ASC";
        $result = $this->db->query($sql);
        $participants = [];
        
        while ($row = $result->fetch_assoc()) {
            $participants[] = $row;
        }
        
        return $participants;
    }
    
    /**
     * Get participant by ID
     */
    public function getById($id) {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        $sql = "SELECT 
                    d.id,
                    d.naam AS name,
                    d.lidnummer AS number,
                    d.geslacht,
                    g.naam AS group_name,
                    g.id AS group_id
                FROM deelnemer d
                LEFT JOIN groep g ON d.groep_id = g.id
                WHERE d.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Create new participant
     */
    public function create($name, $number, $group_name, $gender = 'Heren') {
        $validation = $this->validateParticipantInput($name, $number, $group_name, $gender);
        if ($validation !== null) {
            return ['success' => false, 'error' => $validation];
        }

        $name = trim((string) $name);
        $group_name = trim((string) $group_name);
        $number = (int) $number;
        $groepId = $this->resolveGroupId($group_name, $gender);

        $sql = "INSERT INTO deelnemer (naam, lidnummer, geslacht, groep_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sisi", $name, $number, $gender, $groepId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $this->db->insert_id];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Update participant
     */
    public function update($id, $name, $number, $group_name, $gender = 'Heren') {
        $id = (int) $id;
        if ($id <= 0) {
            return ['success' => false, 'error' => 'Ongeldige deelnemer ID'];
        }

        if ($this->getById($id) === null) {
            return ['success' => false, 'error' => 'Deelnemer niet gevonden'];
        }

        $validation = $this->validateParticipantInput($name, $number, $group_name, $gender);
        if ($validation !== null) {
            return ['success' => false, 'error' => $validation];
        }

        $name = trim((string) $name);
        $group_name = trim((string) $group_name);
        $number = (int) $number;
        $groepId = $this->resolveGroupId($group_name, $gender);

        $sql = "UPDATE deelnemer SET naam = ?, lidnummer = ?, geslacht = ?, groep_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sisii", $name, $number, $gender, $groepId, $id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Delete participant
     */
    public function delete($id) {
        $id = (int) $id;
        if ($id <= 0) {
            return ['success' => false, 'error' => 'Ongeldige deelnemer ID'];
        }

        if ($this->getById($id) === null) {
            return ['success' => false, 'error' => 'Deelnemer niet gevonden'];
        }

        try {
            $this->db->begin_transaction();

            $deleteScoresSql = "DELETE FROM score WHERE deelnemer_id = ?";
            $deleteScoresStmt = $this->db->prepare($deleteScoresSql);
            $deleteScoresStmt->bind_param("i", $id);
            if (!$deleteScoresStmt->execute()) {
                $this->db->rollback();
                return ['success' => false, 'error' => $deleteScoresStmt->error];
            }

            $sql = "DELETE FROM deelnemer WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                $this->db->rollback();
                return ['success' => false, 'error' => $stmt->error];
            }

            $this->db->commit();
            return ['success' => true];
        } catch (Throwable $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function resolveGroupId($groupName, $gender) {
        $groupName = trim((string) $groupName);
        if ($groupName === '') {
            return null;
        }

        $lookupSql = "SELECT id FROM groep WHERE LOWER(naam) = LOWER(?) AND geslacht = ? LIMIT 1";
        $lookup = $this->db->prepare($lookupSql);
        $lookup->bind_param("ss", $groupName, $gender);
        $lookup->execute();
        $existing = $lookup->get_result()->fetch_assoc();

        if ($existing) {
            return (int) $existing['id'];
        }

        $insertSql = "INSERT INTO groep (naam, geslacht, wedstrijd_id) VALUES (?, ?, ?)";
        $insert = $this->db->prepare($insertSql);
        $wedstrijdId = self::DEFAULT_WEDSTRIJD_ID;
        $insert->bind_param("ssi", $groupName, $gender, $wedstrijdId);

        if ($insert->execute()) {
            return (int) $this->db->insert_id;
        }

        return null;
    }

    private function validateParticipantInput($name, $number, $group_name, $gender) {
        $name = trim((string) $name);
        $group_name = trim((string) $group_name);
        $gender = trim((string) $gender);

        if ($name === '') {
            return 'Naam is verplicht';
        }

        if (strlen($name) > self::NAME_MAX_LENGTH) {
            return 'Naam is te lang';
        }

        if (!in_array($gender, self::ALLOWED_GENDERS, true)) {
            return 'Ongeldige geslachtwaarde';
        }

        if (!ctype_digit((string) $number) || (int) $number <= 0) {
            return 'Lidnummer moet een positief geheel getal zijn';
        }

        if ($group_name !== '' && strlen($group_name) > self::GROUP_MAX_LENGTH) {
            return 'Groepsnaam is te lang';
        }

        return null;
    }
}
?>
