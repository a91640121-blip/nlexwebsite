<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../vendor_autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Classes\Database;
use App\Classes\Message;
use App\Classes\User;

$config = require __DIR__ . '/../config/config.php';
$db = Database::getConnection($config);

$userModel = new User($db);
$currentUser = null;
if (!empty($_SESSION['user_id'])) {
    $currentUser = $userModel->getById((int)$_SESSION['user_id']);
}


// Only allow users with role 'admin'
if (!$currentUser || strtolower($currentUser['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo "<h1>403 Forbidden</h1><p>You do not have access to this page.</p>";
  exit;
}

$messageModel = new Message($db);
$messages = $messageModel->getAllWithUsers();

// Load users list for admin management
$allUsers = [];
try {
  $stmt = $db->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC');
  $allUsers = $stmt->fetchAll();
} catch (Exception $e) {
  $allUsers = [];
}

require __DIR__ . '/../inc/header.php';
?>
<main class="min-h-screen p-8 bg-gray-100">
  <div class="max-w-5xl mx-auto">
  <header class="flex items-center gap-4 mb-6">
    <?php $adminLogo = (isset($logoPath) ? $logoPath : '/nlexwebsite/logo.png'); ?>
    <img src="<?=htmlspecialchars($adminLogo)?>" alt="logo" class="h-12 w-12 object-contain">
    <div>
      <h1 class="text-2xl font-bold">NLEX Dashboard</h1>
      <p class="text-sm text-gray-600">Manage messages and users</p>
    </div>
  </header>

    <div class="mb-4">
      <button id="tabMsgs" class="px-3 py-1 bg-white text-black rounded mr-2">Messages</button>
      <button id="tabUsers" class="px-3 py-1 bg-gray-200 text-black rounded">Users</button>
    </div>

    <div id="panelMessages">
      <h2 class="text-xl font-semibold mb-3">Messages</h2>
      <div class="card p-4 shadow-soft">
      <?php if (empty($messages)): ?>
        <div class="p-4">No messages yet.</div>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($messages as $m): ?>
            <div class="p-4 bg-white rounded shadow" data-msgid="<?=htmlspecialchars($m['id'])?>">
              <div class="flex justify-between items-start">
                <div>
                  <div class="text-sm text-gray-600">From: <strong><?=htmlspecialchars($m['name'] ?? '')?></strong> &lt;<?=htmlspecialchars($m['email'] ?? '')?>&gt; <span class="ml-2 inline-block text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800">User</span></div>
                  <?php if (!empty($m['user_id'])): ?>
                    <div class="text-xs text-gray-500">Account: <?=htmlspecialchars($m['full_name'] ?? $m['user_email'] ?? '')?> (user id <?=htmlspecialchars($m['user_id'])?>)</div>
                  <?php endif; ?>
                </div>
                <div class="text-xs text-gray-500"><?=htmlspecialchars($m['created_at'] ?? '')?></div>
              </div>
              <div class="mt-3 whitespace-pre-wrap text-gray-800"><?=nl2br(htmlspecialchars($m['message'] ?? ''))?></div>
              <div class="mt-3 flex items-center gap-2">
                <?php if (empty($m['reply'])): ?>
                  <div class="w-full">
                    <button class="replyBtn px-3 py-1 bg-teal-500 text-white rounded" data-id="<?=htmlspecialchars($m['id'])?>">Reply</button>

                    <form class="admin-reply-form hidden mt-3" data-id="<?=htmlspecialchars($m['id'])?>">
                      <textarea name="reply" placeholder="Write your reply to the user..." class="w-full px-3 py-2 rounded bg-gray-100 text-gray-800" rows="4" required></textarea>
                      <div class="mt-2 flex items-center gap-2">
                        <button type="submit" class="admin-send px-3 py-1 bg-teal-600 text-white rounded">Send</button>
                        <button type="button" class="admin-cancel px-3 py-1 bg-gray-200 text-black rounded">Cancel</button>
                        <span class="admin-reply-status text-sm text-gray-600 hidden"></span>
                      </div>
                    </form>
                  </div>
                <?php else: ?>
                  <div class="p-3 bg-gray-50 rounded">
                    <div class="flex items-center justify-between">
                      <div class="text-xs text-teal-600">Replied: <?=htmlspecialchars($m['replied_at'] ?? '')?></div>
                      <div><span class="inline-block text-xs px-2 py-0.5 rounded bg-yellow-100 text-yellow-800">NLEX</span></div>
                    </div>
                    <div class="mt-1 text-gray-800"><?=nl2br(htmlspecialchars($m['reply']))?></div>
                    <?php
                      // Show any user reply if available
                      $userReply = $m['user_reply'] ?? null;
                      $userReplyAt = $m['user_replied_at'] ?? null;
                      if (!$userReply) {
                        // try fallback: appended reply marker in message
                        if (!empty($m['message']) && strpos($m['message'], '--- User reply ---') !== false) {
                          $parts = explode('--- User reply ---', $m['message'], 2);
                          $userReply = trim($parts[1] ?? '');
                          // in fallback we don't have a timestamp, leave null
                        }
                      }
                      if ($userReply) {
                        $userName = htmlspecialchars($m['full_name'] ?? $m['user_email'] ?? $m['name'] ?? 'User');
                        echo '<div class="mt-3 p-3 bg-white border rounded">';
                        echo '<div class="text-xs text-gray-600">Reply from: <strong>'.$userName.'</strong>';
                        if (!empty($userReplyAt)) echo ' <span class="ml-2 text-xs text-gray-500">('.htmlspecialchars($userReplyAt).')</span>';
                        echo '</div>';
                        echo '<div class="mt-2 text-gray-800">'.nl2br(htmlspecialchars($userReply)).'</div>';
                        echo '</div>';
                      }
                    ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      </div>
    </div>

    <div id="panelUsers" class="hidden">
      <h2 class="text-xl font-semibold mb-3">Registered Users</h2>
      <?php if (empty($allUsers)): ?>
        <div class="p-4 bg-white rounded shadow">No users found.</div>
      <?php else: ?>
        <div class="bg-white rounded shadow overflow-hidden">
          <table class="min-w-full text-left">
            <thead class="bg-gray-100"><tr><th class="p-3">ID</th><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Role</th><th class="p-3">Joined</th><th class="p-3">Actions</th></tr></thead>
            <tbody>
              <?php foreach ($allUsers as $u): ?>
                <tr class="border-t"><td class="p-3"><?=htmlspecialchars($u['id'])?></td><td class="p-3"><?=htmlspecialchars($u['full_name'])?></td><td class="p-3"><?=htmlspecialchars($u['email'])?></td><td class="p-3"><?=htmlspecialchars($u['role'])?></td><td class="p-3"><?=htmlspecialchars($u['created_at'])?></td>
                <td class="p-3">
                  <button class="btnChangePwd px-2 py-1 bg-yellow-200 text-black rounded" data-user="<?=htmlspecialchars($u['id'])?>">Set Password</button>
                </td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../inc/footer.php';

/* Inline JS for tab switching and admin actions */
?>
<script>
document.getElementById('tabMsgs').addEventListener('click', function(){
  document.getElementById('panelMessages').classList.remove('hidden');
  document.getElementById('panelUsers').classList.add('hidden');
});
document.getElementById('tabUsers').addEventListener('click', function(){
  document.getElementById('panelMessages').classList.add('hidden');
  document.getElementById('panelUsers').classList.remove('hidden');
});

document.querySelectorAll('.btnChangePwd').forEach(btn=>{
  btn.addEventListener('click', async function(){
    const uid = this.getAttribute('data-user');
    const newPwd = prompt('Enter a new password for user ID '+uid+':');
    if (!newPwd) return;
    const res = await fetch('../auth/auth.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'change_password_admin', user_id:uid, new_password:newPwd})});
    const j = await res.json();
    if (j.success) alert('Password updated'); else alert(j.message || 'Failed');
  });
});
</script>
<script>
document.querySelectorAll('.replyBtn').forEach(b=>{
  b.addEventListener('click', function(){
    const id = this.getAttribute('data-id');
    const form = document.querySelector('.admin-reply-form[data-id="'+id+'"]');
    if (!form) return;
    form.classList.remove('hidden');
    form.querySelector('textarea').focus();
  });
});

// Cancel buttons
document.querySelectorAll('.admin-cancel').forEach(btn=>{
  btn.addEventListener('click', function(){
    const form = this.closest('.admin-reply-form');
    if (form) { form.classList.add('hidden'); form.querySelector('textarea').value = ''; }
  });
});

// Submit handlers
document.querySelectorAll('.admin-reply-form').forEach(form=>{
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    const id = this.getAttribute('data-id');
    const textarea = this.querySelector('textarea');
    const status = this.querySelector('.admin-reply-status');
    const sendBtn = this.querySelector('.admin-send');
    status.classList.remove('hidden'); status.textContent = 'Sending...'; sendBtn.disabled = true;
    try {
      const res = await fetch('reply.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({message_id: id, reply: textarea.value})});
      const j = await res.json();
      if (j.success) {
        status.textContent = 'Reply saved';
        location.reload();
      } else {
        status.textContent = j.message || 'Failed to save reply';
      }
    } catch (err) { status.textContent = 'Network error'; }
    finally { sendBtn.disabled = false; }
  });
});
</script>
