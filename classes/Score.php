<?php
/**
 * Score Class
 * Handles score operations
 */

class Score {
    private $db;

    private const STATUS_INGEVOERD = 1;
    private const STATUS_GOEDGEKEURD = 2;
    private const STATUS_AANGEPAST = 3;
    private const STATUS_AFGEKEURD = 4;
    private const MIN_SCORE = 0.0;
    private const MAX_SCORE = 10.0;
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    /**
     * Get pending scores
     */
    public function getPending() {
        $sql = "SELECT 
                s.id,
                s.d_score,
                s.e_score,
                s.n_score,
                s.totaal_score AS total,
                s.ingevoerd_op AS submitted_at,
                d.naam AS name,
                d.lidnummer AS number,
                o.naam AS apparatus_name,
                ss.status AS status_text
            FROM score s
            JOIN deelnemer d ON s.deelnemer_id = d.id
            JOIN onderdeel o ON s.onderdeel_id = o.id
            JOIN score_status ss ON s.status_id = ss.id
            WHERE s.status_id IN (?, ?)
            ORDER BY s.ingevoerd_op DESC";
        $stmt = $this->db->prepare($sql);
        $statusIngevoerd = self::STATUS_INGEVOERD;
        $statusAangepast = self::STATUS_AANGEPAST;
        $stmt->bind_param("ii", $statusIngevoerd, $statusAangepast);
        $stmt->execute();
        $result = $stmt->get_result();
        $scores = [];
        
        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }
        
        return $scores;
    }
    
    /**
     * Get approved scores
     */
    public function getApproved() {
        $sql = "SELECT 
                s.id,
                s.d_score,
                s.e_score,
                s.n_score,
                s.totaal_score AS total,
                s.ingevoerd_op AS submitted_at,
                d.naam AS name,
                d.lidnummer AS number,
                o.naam AS apparatus_name
            FROM score s
            JOIN deelnemer d ON s.deelnemer_id = d.id
            JOIN onderdeel o ON s.onderdeel_id = o.id
            WHERE s.status_id = ?
            ORDER BY s.totaal_score DESC";

        $stmt = $this->db->prepare($sql);
        $status = self::STATUS_GOEDGEKEURD;
        $stmt->bind_param("i", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $scores = [];
        
        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }
        
        return $scores;
    }
    
    /**
     * Submit new score
     */
    public function submit($participant_id, $apparatus_id, $d_score, $e_score, $n_score, $jury_id = 1) {
        $participant_id = (int) $participant_id;
        $apparatus_id = (int) $apparatus_id;
        $jury_id = (int) $jury_id;

        if ($participant_id <= 0 || $apparatus_id <= 0 || $jury_id <= 0) {
            return ['success' => false, 'error' => 'Ongeldige ID-waarden'];
        }

        $validation = $this->validateScoreValues($d_score, $e_score, $n_score);
        if ($validation !== null) {
            return ['success' => false, 'error' => $validation];
        }

        if (!$this->recordExists('deelnemer', $participant_id)) {
            return ['success' => false, 'error' => 'Deelnemer bestaat niet'];
        }

        if (!$this->recordExists('onderdeel', $apparatus_id)) {
            return ['success' => false, 'error' => 'Onderdeel bestaat niet'];
        }

        $total = $d_score + $e_score - $n_score;
        $total = max(0, $total); // Ensure non-negative
        
        $sql = "INSERT INTO score (deelnemer_id, onderdeel_id, jury_id, d_score, e_score, n_score, totaal_score, status_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $status = self::STATUS_INGEVOERD;
        $stmt->bind_param("iiiddddi", $participant_id, $apparatus_id, $jury_id, $d_score, $e_score, $n_score, $total, $status);
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $this->db->insert_id, 'total' => $total];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Approve score
     */
    public function approve($id, $approved_by = null, $notes = null) {
        $id = (int) $id;
        if ($id <= 0) {
            return ['success' => false, 'error' => 'Ongeldige score ID'];
        }

        $sql = "UPDATE score SET status_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $status = self::STATUS_GOEDGEKEURD;
        $stmt->bind_param("ii", $status, $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows === 0) {
                return ['success' => false, 'error' => 'Score niet gevonden'];
            }
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Reject score
     */
    public function reject($id) {
        $id = (int) $id;
        if ($id <= 0) {
            return ['success' => false, 'error' => 'Ongeldige score ID'];
        }

        $sql = "UPDATE score SET status_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $status = self::STATUS_AFGEKEURD;
        $stmt->bind_param("ii", $status, $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows === 0) {
                return ['success' => false, 'error' => 'Score niet gevonden'];
            }
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Edit score
     */
    public function edit($id, $d_score, $e_score, $n_score, $notes = null) {
        $id = (int) $id;
        if ($id <= 0) {
            return ['success' => false, 'error' => 'Ongeldige score ID'];
        }

        $validation = $this->validateScoreValues($d_score, $e_score, $n_score);
        if ($validation !== null) {
            return ['success' => false, 'error' => $validation];
        }

        if (!$this->recordExists('score', $id)) {
            return ['success' => false, 'error' => 'Score niet gevonden'];
        }

        $total = $d_score + $e_score - $n_score;
        $total = max(0, $total);
        
        $sql = "UPDATE score SET d_score = ?, e_score = ?, n_score = ?, totaal_score = ?, status_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $status = self::STATUS_AANGEPAST;
        $stmt->bind_param("ddddii", $d_score, $e_score, $n_score, $total, $status, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'total' => $total];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Get top 10 scores
     */
    public function getTop10() {
        $sql = "SELECT 
                d.id,
                d.naam AS name,
                d.lidnummer AS number,
                g.naam AS group_name,
                SUM(s.totaal_score) AS total
            FROM score s
            JOIN deelnemer d ON s.deelnemer_id = d.id
            LEFT JOIN groep g ON d.groep_id = g.id
            WHERE s.status_id = ?
            GROUP BY d.id, d.naam, d.lidnummer, g.naam
            ORDER BY total DESC
            LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $status = self::STATUS_GOEDGEKEURD;
        $stmt->bind_param("i", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $scores = [];
        
        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }
        
        return $scores;
    }
    
    /**
     * Get scores by participant
     */
    public function getByParticipant($participant_id) {
        $participant_id = (int) $participant_id;
        if ($participant_id <= 0) {
            return [];
        }

        $sql = "SELECT 
                    s.id,
                    s.d_score,
                    s.e_score,
                    s.n_score,
                    s.totaal_score AS total,
                    s.ingevoerd_op AS submitted_at,
                    o.naam AS apparatus_name,
                    ss.status AS status_text
                FROM score s
                JOIN onderdeel o ON s.onderdeel_id = o.id
                JOIN score_status ss ON s.status_id = ss.id
                WHERE s.deelnemer_id = ?
                ORDER BY s.ingevoerd_op DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $participant_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $scores = [];
        
        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }
        
        return $scores;
    }

    /**
     * Get latest approved score for main display
     */
    public function getLatestApproved() {
        $sql = "SELECT 
                    s.id,
                    s.d_score,
                    s.e_score,
                    s.n_score,
                    s.totaal_score AS total,
                    s.ingevoerd_op AS submitted_at,
                    d.naam AS name,
                    d.lidnummer AS number,
                    o.naam AS apparatus_name
                FROM score s
                JOIN deelnemer d ON s.deelnemer_id = d.id
                JOIN onderdeel o ON s.onderdeel_id = o.id
                WHERE s.status_id = ?
                ORDER BY s.ingevoerd_op DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $status = self::STATUS_GOEDGEKEURD;
        $stmt->bind_param("i", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function validateScoreValues($d_score, $e_score, $n_score) {
        $d = filter_var($d_score, FILTER_VALIDATE_FLOAT);
        $e = filter_var($e_score, FILTER_VALIDATE_FLOAT);
        $n = filter_var($n_score, FILTER_VALIDATE_FLOAT);

        if ($d === false || $e === false || $n === false) {
            return 'Alle scores moeten numeriek zijn';
        }

        if ($d < self::MIN_SCORE || $d > self::MAX_SCORE ||
            $e < self::MIN_SCORE || $e > self::MAX_SCORE ||
            $n < self::MIN_SCORE || $n > self::MAX_SCORE) {
            return 'Scores moeten tussen 0 en 10 liggen';
        }

        return null;
    }

    private function recordExists($tableName, $id) {
        if (!preg_match('/^[a-z_]+$/i', $tableName)) {
            return false;
        }

        $sql = "SELECT id FROM {$tableName} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return (bool) $stmt->get_result()->fetch_assoc();
    }
}
?>
