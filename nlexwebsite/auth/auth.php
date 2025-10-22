<?php
require_once __DIR__ . '/../vendor_autoload.php';

use App\Classes\Database;
use App\Classes\User;

session_start();

$config = require __DIR__ . '/../config/config.php';
$db = Database::getConnection($config);
$userModel = new User($db);

$action = $_POST['action'] ?? $_GET['action'] ?? null;

header('Content-Type: application/json');

try {
    if ($action === 'register') {
        $fullName = trim($_POST['fullName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($fullName === '' || $email === '' || $password === '') {
            throw new Exception('All fields are required');
        }

        if ($userModel->findByEmail($email)) {
            throw new Exception('Email already registered');
        }

        $id = $userModel->create($fullName, $email, $password);
        $_SESSION['user_id'] = $id;
        echo json_encode(['success' => true, 'user_id' => $id]);
        exit;
    }

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            throw new Exception('Email and password required');
        }

        $user = $userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid credentials');
        }

        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true, 'user_id' => $user['id']]);
        exit;
    }

    if ($action === 'logout') {
        // Clear all session data and destroy the session completely
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Unset all session variables
        $_SESSION = [];
        // If there's a session cookie, delete it
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        // Finally destroy the session
        session_destroy();

        // If request expects JSON (AJAX), return JSON; otherwise redirect back to the site index
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isXhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isXhr || strpos($accept, 'application/json') !== false) {
            echo json_encode(['success' => true]);
            exit;
        }

        // Non-AJAX: redirect back to the index page (auth.php is in /auth/ so go one level up)
        header('Location: ../index.php');
        exit;
    }

    if ($action === 'change_password') {
        if (empty($_SESSION['user_id'])) throw new Exception('Not authenticated');
        $userId = (int)$_SESSION['user_id'];
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if ($old === '' || $new === '') throw new Exception('Old and new passwords are required');
        $userWithHash = $userModel->getByIdWithHash($userId);
        if (!$userWithHash || !password_verify($old, $userWithHash['password_hash'])) throw new Exception('Old password is incorrect');
        $ok = $userModel->updatePassword($userId, $new);
        if (!$ok) throw new Exception('Failed to update password');
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'change_password_admin') {
        // Only admin users can change other users' passwords
        if (empty($_SESSION['user_id'])) throw new Exception('Not authenticated');
        $admin = $userModel->getById((int)$_SESSION['user_id']);
        if (strtolower($admin['role'] ?? '') !== 'admin') throw new Exception('Unauthorized');
        $targetId = (int)($_POST['user_id'] ?? 0);
        $new = $_POST['new_password'] ?? '';
        if ($targetId <= 0 || $new === '') throw new Exception('User ID and new password required');
        $ok = $userModel->updatePassword($targetId, $new);
        if (!$ok) throw new Exception('Failed to update password');
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'No action specified']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
