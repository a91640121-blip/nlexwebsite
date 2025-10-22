<?php
require __DIR__ . '/inc/header.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

use App\Classes\Database;
use App\Classes\User;

$config = require __DIR__ . '/config/config.php';
$db = App\Classes\Database::getConnection($config);
$userModel = new User($db);
$user = $userModel->getById((int)$_SESSION['user_id']);
?>
<main class="min-h-[60vh] bg-gray-900 text-white p-8">
  <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
    <aside class="md:col-span-1 bg-gray-800 p-6 rounded-lg text-center">
      <div class="w-28 h-28 mx-auto rounded-full bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-black text-4xl font-bold mb-4">
        <?php
        // initials as avatar fallback
        $initials = '';
        if (!empty($user['full_name'])) {
            $parts = preg_split('/\s+/', trim($user['full_name']));
            $initials = strtoupper(($parts[0][0] ?? '') . ($parts[1][0] ?? ''));
        }
        echo htmlspecialchars($initials ?: 'U');
        ?>
      </div>
      <h2 class="text-xl font-semibold mb-1"><?=htmlspecialchars($user['full_name'])?></h2>
      <div class="text-sm text-gray-300 mb-3"><?=htmlspecialchars($user['email'])?></div>
      <div class="text-xs text-gray-400">Joined: <?=htmlspecialchars($user['created_at'] ?? '')?></div>
      <a href="index.php" class="inline-block mt-4 px-4 py-2 bg-white text-black rounded">Back to Home</a>
    </aside>

    <section class="md:col-span-2 space-y-6">
      <div class="bg-gray-800 p-6 rounded-lg">
        <h3 class="text-lg font-semibold mb-3">Account Details</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><span class="text-gray-400">Full name</span><div class="mt-1"><?=htmlspecialchars($user['full_name'])?></div></div>
          <div><span class="text-gray-400">Email</span><div class="mt-1"><?=htmlspecialchars($user['email'])?></div></div>
        </div>
      </div>

      <div class="bg-gray-800 p-6 rounded-lg">
        <h3 class="text-lg font-semibold mb-3">Change Password</h3>
        <div id="pwdAlert" class="hidden text-sm mb-3"></div>
        <form id="changePwdForm" class="space-y-3">
          <input type="password" id="old_password" placeholder="Current password" class="w-full px-3 py-2 rounded bg-gray-700" required>
          <input type="password" id="new_password" placeholder="New password" class="w-full px-3 py-2 rounded bg-gray-700" required>
          <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-teal-400 text-black rounded">Change Password</button>
            <div id="pwdSpinner" class="hidden text-gray-400">Processing...</div>
          </div>
        </form>
      </div>
        <div class="bg-gray-800 p-6 rounded-lg">
          <h3 class="text-lg font-semibold mb-3">Message Inbox</h3>
          <?php
          // show messages sent by this user and any admin reply
          $msgModel = new App\Classes\Message($db);
          $inbox = $msgModel->getByUser((int)$_SESSION['user_id']);
          if (empty($inbox)) {
            echo '<div class="text-gray-400">No messages yet.</div>';
          } else {
            echo '<div class="space-y-4">';
            foreach ($inbox as $m) {
              echo '<div class="bg-gray-700 p-3 rounded">';
              echo '<div class="flex items-center justify-between"><div class="text-sm text-gray-300">Sent: '.htmlspecialchars($m['created_at']).'</div><div><span class="inline-block text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800">You</span></div></div>';
              echo '<div class="mt-2 text-gray-100">'.nl2br(htmlspecialchars($m['message'])).'</div>';
              if (!empty($m['reply'])) {
                echo '<div class="mt-3 p-3 bg-gray-600 rounded">';
                    echo '<div class="flex items-center justify-between"><div class="text-xs text-teal-300">Reply from admin ('.htmlspecialchars($m['replied_at'] ?? '').')</div><div><span class="inline-block text-xs px-2 py-0.5 rounded bg-yellow-100 text-yellow-800">NLEX</span></div></div>';
                echo '<div class="mt-1 text-gray-100">'.nl2br(htmlspecialchars($m['reply'])).'</div>';
                // Reply form for the user to respond to admin
                echo '<div class="mt-3 border-t border-gray-500 pt-3">';
                echo '<form class="user-reply-form" data-message-id="'.(int)$m['id'].'">';
                echo '<textarea name="reply" placeholder="Write your reply to NLEX..." class="w-full px-3 py-2 mt-2 rounded bg-gray-700 text-white" rows="3" required></textarea>';
                echo '<div class="mt-2 flex items-center gap-2"><button class="reply-btn px-3 py-1 bg-teal-400 text-black rounded">Send Reply</button><span class="reply-status text-sm text-gray-300 hidden"></span></div>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
              }
              echo '</div>';
            }
            echo '</div>';
          }
          ?>
        </div>
    </section>
  </div>
</main>

<script>
document.getElementById('changePwdForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const old_password = document.getElementById('old_password').value;
  const new_password = document.getElementById('new_password').value;
  const alertEl = document.getElementById('pwdAlert');
  const spinner = document.getElementById('pwdSpinner');
  alertEl.classList.add('hidden'); spinner.classList.remove('hidden');
  try {
    const res = await fetch('auth/auth.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'change_password', old_password, new_password})});
    const data = await res.json();
    spinner.classList.add('hidden');
    if (data.success) {
      alertEl.classList.remove('hidden'); alertEl.classList.remove('text-red-400'); alertEl.classList.add('text-green-300'); alertEl.textContent = 'Password changed successfully';
      document.getElementById('old_password').value = '';
      document.getElementById('new_password').value = '';
      setTimeout(()=> alertEl.classList.add('hidden'), 3000);
    } else {
      alertEl.classList.remove('hidden'); alertEl.classList.remove('text-green-300'); alertEl.classList.add('text-red-400'); alertEl.textContent = data.message || 'Failed to change password';
    }
  } catch (err) {
    spinner.classList.add('hidden');
    alertEl.classList.remove('hidden'); alertEl.classList.add('text-red-400'); alertEl.textContent = 'Network error';
  }
});
</script>

<script>
// Handle user reply forms
document.querySelectorAll('.user-reply-form').forEach(form => {
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    const msgId = this.getAttribute('data-message-id');
    const textarea = this.querySelector('textarea[name="reply"]');
    const status = this.querySelector('.reply-status');
    const btn = this.querySelector('.reply-btn');
    if (!textarea.value.trim()) {
      status.classList.remove('hidden'); status.textContent = 'Please enter a reply'; status.classList.add('text-red-400');
      return;
    }
    status.classList.remove('hidden'); status.textContent = 'Sending...'; status.classList.remove('text-red-400'); status.classList.add('text-gray-300');
    btn.disabled = true;
    try {
      const res = await fetch('reply_user.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({message_id: msgId, reply: textarea.value})
      });
      const data = await res.json();
      if (data.success) {
        status.classList.remove('text-red-400'); status.classList.add('text-green-300'); status.textContent = 'Reply sent';
        textarea.value = '';
      } else {
        status.classList.remove('text-green-300'); status.classList.add('text-red-400'); status.textContent = data.message || 'Failed to send';
      }
    } catch (err) {
      status.classList.remove('text-green-300'); status.classList.add('text-red-400'); status.textContent = 'Network error';
    } finally {
      btn.disabled = false;
      setTimeout(()=> status.classList.add('hidden'), 3000);
    }
  });
});
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
