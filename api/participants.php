<?php
// Participants API - returns JSON for all responses (even on errors)

// Always return JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Do not expose raw PHP errors as HTML
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Exception and error handlers to return JSON
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Interne serverfout']);
    exit;
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Interne serverfout']);
    exit;
});

function positiveInt($value): int {
    $validated = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $validated === false ? 0 : (int) $validated;
}

// Ensure required files exist before including them (avoid fatal include errors)
$baseDir = dirname(__DIR__);
$cfgFile = $baseDir . '/config/database.php';
$dbClass = $baseDir . '/classes/Database.php';
$participantClass = $baseDir . '/classes/Participant.php';

if (!file_exists($cfgFile) || !file_exists($dbClass) || !file_exists($participantClass)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server configuration error']);
    exit;
}

require_once $cfgFile;
require_once $dbClass;
require_once $participantClass;

// Initialize DB and participant handler
$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$participant = new Participant($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $participants = $participant->getAll();
                echo json_encode(['success' => true, 'data' => $participants]);
            } elseif ($action === 'get') {
                $id = positiveInt($_GET['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Ongeldige deelnemer ID']);
                    break;
                }

                $p = $participant->getById($id);
                echo json_encode(['success' => true, 'data' => $p]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            if ($action === 'create') {
                $result = $participant->create(
                    $data['name'] ?? '',
                    $data['number'] ?? '',
                    $data['group'] ?? '',
                    $data['gender'] ?? 'Heren'
                );
                if (!($result['success'] ?? false)) {
                    http_response_code(400);
                }
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            if ($action === 'update') {
                $id = positiveInt($_GET['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Ongeldige deelnemer ID']);
                    break;
                }

                $result = $participant->update(
                    $id,
                    $data['name'] ?? '',
                    $data['number'] ?? '',
                    $data['group'] ?? '',
                    $data['gender'] ?? 'Heren'
                );
                if (!($result['success'] ?? false)) {
                    http_response_code(400);
                }
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action or missing id']);
            }
            break;

        case 'DELETE':
            if ($action === 'delete') {
                $id = positiveInt($_GET['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Ongeldige deelnemer ID']);
                    break;
                }

                $result = $participant->delete($id);
                if (!($result['success'] ?? false)) {
                    http_response_code(400);
                }
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action or missing id']);
            }
            break;

        case 'OPTIONS':
            // CORS preflight handled by headers
            http_response_code(200);
            exit;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Invalid method']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Interne serverfout']);
}

