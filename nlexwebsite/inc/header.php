<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = null;
if (!empty($_SESSION['user_id'])) {
    $config = require __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../vendor_autoload.php';
    $db = App\Classes\Database::getConnection($config);
    $uModel = new App\Classes\User($db);
    $user = $uModel->getById((int)$_SESSION['user_id']);
}
// compute a base path so asset links work from subfolders (e.g. /admin)
$scriptName = str_replace('\\','/', $_SERVER['SCRIPT_NAME'] ?? '');
$baseDir = rtrim(dirname($scriptName), '/');
$baseUrl = ($baseDir === '/' || $baseDir === '.') ? '' : $baseDir;
$logoPath = ($baseUrl ? $baseUrl . '/' : '') . 'logo.png';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NLEX Music</title>
  <!-- Tailwind via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="css/styles.css" rel="stylesheet">
</head>
<body>
  <nav class="bg-teal-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <a href="index.php" class="flex items-center gap-3">
            <img src="<?=htmlspecialchars($logoPath)?>" alt="logo" class="h-10 w-10 object-contain"> <span class="font-semibold text-xl">NLEX MUSIC</span>
          </a>
        </div>
        <div class="hidden md:flex md:items-center md:space-x-6">
          <a href="about.php" class="flex items-center gap-2 hover:text-teal-200"><i class="fa-solid fa-circle-info"></i> <span>About</span></a>
          <a href="index.php#music" class="flex items-center gap-2 hover:text-teal-200"><i class="fa-solid fa-music"></i> <span>Music</span></a>
          <a href="index.php#listen" class="flex items-center gap-2 hover:text-teal-200"><i class="fa-solid fa-headphones"></i> <span>Listen</span></a>
          <a href="index.php#upcoming" class="flex items-center gap-2 hover:text-teal-200"><i class="fa-solid fa-calendar-days"></i> <span>Upcoming</span></a>
          <a href="contact-us.php" class="flex items-center gap-2 hover:text-teal-200"><i class="fa-solid fa-envelope"></i> <span>Contact</span></a>
          <div class="relative">
            <button id="dawBtn" class="hover:text-teal-200">DAW ▾</button>
            <div id="dawMenu" class="hidden absolute right-0 mt-2 w-40 bg-white text-black rounded shadow-lg">
              <a href="ablt.php" class="block px-4 py-2 hover:bg-gray-100">Ableton Live</a>
              <a href="FL.php" class="block px-4 py-2 hover:bg-gray-100">FL Studio</a>
              <a href="lgc.php" class="block px-4 py-2 hover:bg-gray-100">Logic Pro</a>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <?php if ($user): ?>
            <?php if (strtolower($user['role'] ?? '') === 'admin'): ?>
              <a href="admin/messages.php" class="px-3 py-1 rounded-md border border-white/20">NLEX</a>
            <?php endif; ?>
            <a href="profile.php" class="px-3 py-1 rounded-md border border-white/20"><?=htmlspecialchars($user['full_name'])?></a>
            <?php
            // Choose a logout action path that works when the header is included from subfolders (e.g. /admin/)
            $logoutAction = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') === 0) ? '../auth/auth.php' : 'auth/auth.php';
            ?>
            <form method="post" action="<?=htmlspecialchars($logoutAction)?>" style="display:inline-block"><input type="hidden" name="action" value="logout"><button class="px-3 py-1 rounded-md bg-white text-black">Logout</button></form>
          <?php else: ?>
            <a href="sign-up.php" class="px-3 py-1 rounded-md border border-white/20">Sign Up</a>
            <a href="login.php" class="px-3 py-1 rounded-md bg-white text-black">Login</a>
          <?php endif; ?>
          <button id="mobileMenuBtn" class="md:hidden px-2 py-1 rounded-md border border-white/20">Menu</button>
        </div>
      </div>
    </div>
    <div id="mobileMenu" class="hidden md:hidden bg-teal-800 px-4 py-3">
      <a href="about.php" class="block py-1"> <i class="fa-solid fa-circle-info mr-2"></i>About</a>
      <a href="index.php#music" class="block py-1"> <i class="fa-solid fa-music mr-2"></i>Music</a>
      <a href="index.php#listen" class="block py-1"> <i class="fa-solid fa-headphones mr-2"></i>Listen</a>
      <a href="index.php#upcoming" class="block py-1"> <i class="fa-solid fa-calendar-days mr-2"></i>Upcoming</a>
      <a href="contact-us.php" class="block py-1"> <i class="fa-solid fa-envelope mr-2"></i>Contact</a>
      <div class="border-t border-white/10 mt-2 pt-2">
        <a href="ablt.php" class="block py-1">Ableton Live</a>
        <a href="FL.php" class="block py-1">FL Studio</a>
        <a href="lgc.php" class="block py-1">Logic Pro</a>
      </div>
    </div>
  </nav>
  <script>
    document.getElementById('mobileMenuBtn')?.addEventListener('click', ()=>{
      const m = document.getElementById('mobileMenu'); m.classList.toggle('hidden');
    });
    document.getElementById('dawBtn')?.addEventListener('click', ()=>{
      const d = document.getElementById('dawMenu'); d.classList.toggle('hidden');
    });
  </script>
  <script>
    // expose a small currentUser object to client-side scripts
    window.currentUser = <?php echo $user ? json_encode(['id' => $user['id'], 'email' => $user['email'], 'name' => $user['full_name']]) : 'null'; ?>;
  </script>
