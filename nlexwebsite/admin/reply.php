<?php
require_once __DIR__ . '/../vendor_autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Classes\Database;
use App\Classes\Message;
use App\Classes\User;

session_start();
$config = require __DIR__ . '/../config/config.php';
$db = Database::getConnection($config);
$userModel = new User($db);
if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit; }
$admin = $userModel->getById((int)$_SESSION['user_id']);
if (strtolower($admin['role'] ?? '') !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

header('Content-Type: application/json');
try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('POST only');
  $msgId = (int)($_POST['message_id'] ?? 0);
  $reply = trim($_POST['reply'] ?? '');
  if ($msgId <= 0 || $reply === '') throw new Exception('Message ID and reply required');
  $m = new Message($db);
  $ok = $m->replyToMessage($msgId, $reply, (int)$admin['id']);
  if (!$ok) throw new Exception('Failed to save reply');
  echo json_encode(['success'=>true]);
} catch (Exception $e) {
  http_response_code(400); echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
