<?php
/**
 * Apparatus API
 * Handles apparatus operations
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$baseDir = dirname(__DIR__);
require_once $baseDir . '/config/database.php';
require_once $baseDir . '/classes/Database.php';
require_once $baseDir . '/classes/Apparatus.php';

$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$apparatus = new Apparatus($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

function normalizeGender($value): ?string {
    $gender = trim((string) $value);
    if ($gender === 'Heren' || $gender === 'Dames') {
        return $gender;
    }

    return null;
}

try {
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    if ($method === 'GET' && $action === 'list') {
        $gender = normalizeGender($_GET['gender'] ?? '');
        $data = $gender !== null
            ? $apparatus->getByGender($gender)
            : $apparatus->getAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
