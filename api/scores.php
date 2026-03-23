<?php
/**
 * Scores API
 * Handles score operations
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

function respond(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function positiveInt($value): int {
    $validated = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $validated === false ? 0 : (int) $validated;
}

function readJsonBody(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function normalizeGender($value): ?string {
    $gender = trim((string) $value);
    if ($gender === 'Heren' || $gender === 'Dames') {
        return $gender;
    }

    return null;
}

$baseDir = dirname(__DIR__);
require_once $baseDir . '/config/database.php';
require_once $baseDir . '/classes/Database.php';
require_once $baseDir . '/classes/Score.php';

$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$score = new Score($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    if ($method === 'OPTIONS') {
        respond(['success' => true], 200);
    }

    switch ($method) {
        case 'GET':
            if ($action === 'pending') {
                $scores = $score->getPending();
                respond(['success' => true, 'data' => $scores]);
            } elseif ($action === 'approved') {
                $scores = $score->getApproved();
                respond(['success' => true, 'data' => $scores]);
            } elseif ($action === 'top10') {
                $gender = normalizeGender($_GET['gender'] ?? '');
                $scores = $score->getTop10($gender);
                respond(['success' => true, 'data' => $scores]);
            } elseif ($action === 'current') {
                $gender = normalizeGender($_GET['gender'] ?? '');
                $current = $score->getLatestApproved($gender);
                respond(['success' => true, 'data' => $current]);
            } elseif ($action === 'by-participant') {
                $participantId = positiveInt($_GET['id'] ?? 0);
                if ($participantId <= 0) {
                    respond(['success' => false, 'error' => 'Ongeldige deelnemer ID'], 400);
                }

                $scores = $score->getByParticipant($participantId);
                respond(['success' => true, 'data' => $scores]);
            } else {
                respond(['success' => false, 'error' => 'Invalid action'], 400);
            }
            break;
            
        case 'POST':
            $data = readJsonBody();
            
            if ($action === 'submit') {
                $participantId = positiveInt($data['participant_id'] ?? 0);
                $apparatusId = positiveInt($data['apparatus_id'] ?? 0);
                $juryId = positiveInt($data['jury_id'] ?? 1);

                if ($participantId <= 0 || $apparatusId <= 0) {
                    respond(['success' => false, 'error' => 'Deelnemer en onderdeel zijn verplicht'], 400);
                }

                $result = $score->submit(
                    $participantId,
                    $apparatusId,
                    floatval($data['d_score'] ?? 0),
                    floatval($data['e_score'] ?? 0),
                    floatval($data['n_score'] ?? 0),
                    $juryId
                );

                if (!($result['success'] ?? false)) {
                    respond($result, 400);
                }

                respond($result);
            } else {
                respond(['success' => false, 'error' => 'Invalid action'], 400);
            }
            break;
            
        case 'PUT':
            $data = readJsonBody();
            $scoreId = positiveInt($_GET['id'] ?? 0);

            if ($scoreId <= 0) {
                respond(['success' => false, 'error' => 'Ongeldige score ID'], 400);
            }
            
            if ($action === 'approve') {
                $result = $score->approve(
                    $scoreId,
                    $data['approved_by'] ?? null,
                    $data['notes'] ?? null
                );
                respond($result, ($result['success'] ?? false) ? 200 : 400);
            } elseif ($action === 'reject') {
                $result = $score->reject($scoreId);
                respond($result, ($result['success'] ?? false) ? 200 : 400);
            } elseif ($action === 'edit') {
                $result = $score->edit(
                    $scoreId,
                    floatval($data['d_score'] ?? 0),
                    floatval($data['e_score'] ?? 0),
                    floatval($data['n_score'] ?? 0),
                    $data['notes'] ?? null
                );
                respond($result, ($result['success'] ?? false) ? 200 : 400);
            } else {
                respond(['success' => false, 'error' => 'Invalid action'], 400);
            }
            break;
            
        default:
            respond(['success' => false, 'error' => 'Invalid method'], 405);
    }
} catch (Throwable $e) {
    respond(['success' => false, 'error' => 'Interne serverfout'], 500);
}
?>
