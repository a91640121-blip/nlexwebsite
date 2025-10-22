<?php require __DIR__ . '/inc/header.php'; ?>

<div class="min-h-[60vh] flex items-center justify-center px-4">
  <div class="w-full max-w-md bg-gray-800 rounded-lg p-8 text-white shadow">
    <h3 class="text-center text-2xl font-semibold mb-4">Sign Up for NLEX MUSIC</h3>
    <div id="alertMessage" class="hidden bg-red-600 text-white p-2 rounded mb-3"></div>
    <form id="signupForm" class="space-y-4">
      <input type="text" id="fullName" class="w-full px-3 py-2 rounded bg-gray-700" placeholder="Full Name">
      <input type="email" id="email" class="w-full px-3 py-2 rounded bg-gray-700" placeholder="Email">
      <input type="password" id="password" class="w-full px-3 py-2 rounded bg-gray-700" placeholder="Password">
      <button type="submit" class="w-full bg-teal-500 hover:bg-teal-400 text-black font-semibold py-2 rounded">Sign Up</button>
    </form>
    <p class="mt-4 text-center text-sm">Already have an account? <a href="login.php" class="text-teal-300">Login</a></p>
  </div>
</div>

<script>
document.getElementById('signupForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const a = document.getElementById('alertMessage');
  a.classList.add('hidden');
  const fullName = document.getElementById('fullName').value.trim();
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;

  // Basic client-side validation (show inline message instead of browser tooltip)
  if (!fullName) { a.textContent = 'Please enter your full name.'; a.classList.remove('hidden'); return; }
  if (!email) { a.textContent = 'Please enter your email address.'; a.classList.remove('hidden'); return; }
  if (!password) { a.textContent = 'Please enter a password.'; a.classList.remove('hidden'); return; }

  const res = await fetch('auth/auth.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'register', fullName, email, password})});
  const data = await res.json();
  if (data.success) {
    a.classList.remove('hidden'); a.classList.remove('bg-red-600'); a.classList.add('bg-green-600'); a.textContent = 'Registration successful! Redirecting...';
    setTimeout(()=> window.location.href = 'index.php', 1200);
  } else {
    a.classList.remove('hidden'); a.classList.add('bg-red-600'); a.textContent = data.message || 'Signup failed';
  }
});
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
