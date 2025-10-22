<?php
require_once __DIR__ . '/vendor_autoload.php';

use App\Classes\Database;
use App\Classes\Message;

$config = require __DIR__ . '/config/config.php';
$db = Database::getConnection($config);
$msgModel = new Message($db);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST allowed');
    }

    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'You must be logged in to send a message']);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        throw new Exception('All fields are required');
    }

    $userId = (int)$_SESSION['user_id'];
    $id = $msgModel->create($name, $email, $message, $userId);
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
