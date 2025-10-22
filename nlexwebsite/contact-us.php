<?php require __DIR__ . '/inc/header.php'; ?>

<main class="min-h-[60vh] flex items-center justify-center bg-gray-900 text-white p-8">
  <div class="w-full max-w-2xl">
  <h1 class="text-3xl font-bold mb-4 flex items-center gap-3"><i class="fa-solid fa-envelope-open-text text-teal-300"></i> <span id="contactTitle">Contact Us</span></h1>
  <p id="contactSubtitle" class="text-gray-300 mb-6">Have questions or want to collaborate? Send a message and we'll get back to you.</p>

  <div id="contactNotification" class="hidden bg-green-800 text-green-100 p-3 rounded mb-4">Your message has been sent!</div>
  <div id="contactError" class="hidden bg-red-700 text-red-100 p-3 rounded mb-4"></div>
  <div id="contactAlert" class="hidden bg-yellow-600 text-black p-3 rounded mb-4">You must be logged in to send a message. <a href="login.php" class="underline font-semibold">Login</a> or <a href="sign-up.php" class="underline font-semibold">Create an account</a>.</div>
  <form id="contactFormPage" class="space-y-3 bg-gray-800 p-6 rounded">
      <input type="text" id="name" name="name" placeholder="Your Name" class="w-full px-3 py-2 rounded bg-gray-700" required>
      <input type="email" id="email" name="email" placeholder="Your Email" class="w-full px-3 py-2 rounded bg-gray-700" required>
      <textarea id="message" name="message" placeholder="Your Message" class="w-full px-3 py-2 rounded bg-gray-700" style="min-height:140px" required></textarea>
      <button type="submit" class="w-full bg-teal-400 text-black font-semibold py-2 rounded">Send Message</button>
    </form>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const formEl = document.getElementById('contactFormPage');
  const alertEl = document.getElementById('contactAlert');

  // If user not logged in, show alert and disable form
  if (!window.currentUser) {
    alertEl.classList.remove('hidden');
    formEl.querySelectorAll('input,textarea,button').forEach(el => el.setAttribute('disabled','disabled'));
    return;
  }

  formEl.addEventListener('submit', async function(e){
    e.preventDefault();
    // clear notifications
    const note = document.getElementById('contactNotification');
    const err = document.getElementById('contactError');
    note.classList.add('hidden'); err.classList.add('hidden'); err.textContent = '';

    // HTML5 validity check
    if (!formEl.checkValidity()) {
      formEl.reportValidity();
      return;
    }

    const form = e.target; const data = new URLSearchParams(new FormData(form));
    try {
      const res = await fetch('contact.php', {method:'POST', body: data});
      const json = await res.json();
      if (res.status === 401) {
        err.classList.remove('hidden'); err.textContent = json.message || 'You must be logged in to send a message';
        setTimeout(()=> location.href = 'login.php', 1200);
        return;
      }
      if (json.success) {
        note.classList.remove('hidden'); form.reset(); setTimeout(()=> note.classList.add('hidden'), 3000);
      } else {
        err.classList.remove('hidden'); err.textContent = json.message || 'Error sending message';
      }
    } catch (ex) {
      err.classList.remove('hidden'); err.textContent = 'Network error. Please try again.';
    }
  });
  // Personalize title/subtitle when user is logged in
  try {
    const titleEl = document.getElementById('contactTitle');
    const subtitleEl = document.getElementById('contactSubtitle');
    if (window.currentUser) {
      // Use first name if available
      const name = window.currentUser.name || window.currentUser.email || 'there';
      const first = name.split(' ')[0];
      titleEl.textContent = `Hi, ${first}!`;
      subtitleEl.textContent = "Have questions or want to collaborate? Send a message and we'll get back to you.";
    } else {
      titleEl.textContent = 'Contact Us';
      subtitleEl.textContent = "Have questions or want to collaborate? Send a message and we'll get back to you.";
    }
  } catch (e) {
    // ignore personalization errors
  }
});
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
