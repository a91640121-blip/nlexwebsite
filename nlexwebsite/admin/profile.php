<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../vendor_autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Classes\Database;
use App\Classes\User;

$config = require __DIR__ . '/../config/config.php';
$db = Database::getConnection($config);
$userModel = new User($db);

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php'); exit;
}

$currentUser = $userModel->getById((int)$_SESSION['user_id']);
if (strtolower($currentUser['role'] ?? '') !== 'admin') {
    http_response_code(403); echo "Access denied"; exit;
}

// Admin profile page — show admin controls with icon-only button
require __DIR__ . '/../inc/header.php';
?>
<main class="min-h-[60vh] bg-gray-100 p-8">
  <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white text-2xl font-bold">ADM</div>
        <div>
          <h1 class="text-2xl font-semibold">NLEX</h1>
          <div class="text-sm text-gray-500"><?=htmlspecialchars($currentUser['email'])?></div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <!-- Single icon-only admin button -->
        <a href="messages.php" class="icon-btn" title="Messages (admin)"><i class="fa-solid fa-envelope-open-text fa-lg"></i></a>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="col-span-2 bg-gray-50 p-4 rounded">
  <h2 class="text-lg font-semibold mb-2">NLEX Controls</h2>
        <p class="text-sm text-gray-600">Use the icons above to navigate messages, users, and profile settings. The admin panel provides quick actions to manage users and view messages.</p>
        <div class="mt-4 flex flex-wrap gap-3">
          <a href="messages.php" class="px-3 py-2 bg-teal-500 text-white rounded flex items-center gap-2"><i class="fa-solid fa-envelope"></i> View Messages</a>
          <a href="users.php" class="px-3 py-2 bg-blue-500 text-white rounded flex items-center gap-2"><i class="fa-solid fa-users"></i> Manage Users</a>
          <a href="profile.php" class="px-3 py-2 bg-gray-700 text-white rounded flex items-center gap-2"><i class="fa-solid fa-key"></i> Change Password</a>
        </div>
      </div>

      <aside class="bg-white border p-4 rounded">
        <h3 class="text-sm font-semibold text-gray-600">Quick Info</h3>
        <div class="text-sm text-gray-800 mt-2">Logged in as <strong><?=htmlspecialchars($currentUser['email'])?></strong></div>
        <div class="text-xs text-gray-500 mt-1">Role: <?=htmlspecialchars($currentUser['role'] ?? '')?></div>
      </aside>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../inc/footer.php'; ?>
