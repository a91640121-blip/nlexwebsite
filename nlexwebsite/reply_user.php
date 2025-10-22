<?php
require_once __DIR__ . '/vendor_autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Classes\Database;
use App\Classes\Message;

session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
$reply = trim($_POST['reply'] ?? '');

if ($message_id <= 0 || $reply === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $config = require __DIR__ . '/config/config.php';
    $db = Database::getConnection($config);
    $msgModel = new Message($db);
    $ok = $msgModel->userReplyToMessage($message_id, (int)$_SESSION['user_id'], $reply);
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Reply sent']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save reply']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
