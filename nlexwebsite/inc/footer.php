  <footer class="mt-8 bg-gray-900 text-gray-300">
    <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <?php
        // attempt to use same base calculation as header if available
        $footerLogo = isset($logoPath) ? $logoPath : 'logo.png';
        ?>
        <img src="<?=htmlspecialchars($footerLogo)?>" alt="logo" class="h-8 w-8">
        <div>
          <p class="font-semibold">NLEX Music</p>
          <p class="text-sm text-gray-400">© Develop by:Noli Rodenas</p>
          <p class="text-sm text-gray-400">© 2025 NLEX Music</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <a href="https://open.spotify.com/artist/2B1fSeyqDOgyXLXrWvERDx" target="_blank" class="text-teal-300 hover:text-teal-100"><i class="fa-brands fa-spotify fa-lg"></i></a>
        <a href="https://music.apple.com/us/artist/nlex/1670314393" target="_blank" class="text-red-400 hover:text-red-200"><i class="fa-brands fa-apple fa-lg"></i></a>
        <a href="https://on.soundcloud.com/XFPN2C6ARR1yCbfw5" target="_blank" class="text-orange-400 hover:text-orange-200"><i class="fa-brands fa-soundcloud fa-lg"></i></a>
        <a href="https://www.instagram.com/nlex_9/" target="_blank" class="text-pink-400 hover:text-pink-200"><i class="fa-brands fa-instagram fa-lg"></i></a>
      </div>
    </div>
  </footer>
</body>
</html>
