<?php require __DIR__ . '/inc/header.php'; ?>

<!-- Hero -->
<header class="bg-cover bg-center" style="background-image:url('image.jpg')">
  <div class="bg-black/60">
    <div class="max-w-7xl mx-auto py-24 px-4 text-center text-white">
      <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Welcome to NLEX Music</h1>
      <p class="text-lg md:text-xl">Progressive House Producer | Programmer | Dreamer</p>
    </div>
  </div>
</header>

<!-- Upcoming -->
<section id="upcoming" class="py-12 bg-gradient-to-b from-black to-gray-900 text-white">
  <div class="max-w-7xl mx-auto px-4">
    <h2 class="text-3xl font-bold text-teal-400 text-center mb-8">Upcoming Release</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 justify-items-center">
      <div class="w-full max-w-sm bg-gray-800 rounded-lg overflow-hidden shadow-lg">
        <img src="css/upcoming.png" alt="Album Art" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="text-xl font-semibold text-teal-200">Through The Sky</h3>
          <p class="text-sm text-gray-300">Label: Rivaside Records</p>
          <p class="text-sm text-gray-300">Release Date: December 05, 2025</p>
          <div class="mt-4">
            <button id="followBtn" class="w-full bg-teal-400 hover:bg-teal-300 text-black font-semibold py-2 rounded">Pre-Save</button>
            <div id="notification" class="hidden mt-3 text-sm text-green-300">✅ Pre Saving Redirecting to Spotify...</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.getElementById('followBtn')?.addEventListener('click', function(){
  const n = document.getElementById('notification'); n.classList.remove('hidden');
  setTimeout(()=> window.open('https://open.spotify.com/artist/2B1fSeyqDOgyXLXrWvERDx','_blank'), 1500);
});
</script>

<!-- Listen -->
<section id="listen" class="py-12 bg-gray-800 text-white">
  <div class="max-w-4xl mx-auto px-4 text-center">
    <h2 class="text-2xl font-bold mb-4">Listen On</h2>
    <div class="flex flex-wrap justify-center gap-4">
      <a href="https://open.spotify.com/artist/2B1fSeyqDOgyXLXrWvERDx" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-teal-400 to-teal-300 text-black rounded shadow hover:scale-105 transition-transform"><i class="fa-brands fa-spotify"></i> Spotify</a>
      <a href="https://music.apple.com/us/artist/nlex/1670314393" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-400 text-white rounded shadow hover:scale-105 transition-transform"><i class="fa-brands fa-apple"></i> Apple Music</a>
      <a href="https://on.soundcloud.com/XFPN2C6ARR1yCbfw5" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-400 to-orange-300 text-black rounded shadow hover:scale-105 transition-transform"><i class="fa-brands fa-soundcloud"></i> SoundCloud</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
