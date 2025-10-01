<?php
require_once __DIR__ . '/../src/helpers.php';
?>
</main>
<footer class="border-t border-white/10 bg-black/30 backdrop-blur-xl">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10 grid gap-8 md:grid-cols-3 text-sm">
    <div class="space-y-3">
      <div class="flex items-center gap-3">
        <img src="<?= asset('assets/images/logo.png') ?>" class="w-9 h-9" alt="logo">
        <div>
          <div class="text-lg font-semibold">RaceVerse</div>
          <div class="text-xs text-white/50">RaceVerse BASIC gratuito, upgrade PRO a €2,99/mese</div>
        </div>
      </div>
      <p class="text-white/60 leading-relaxed">Hotlap curati, analisi affidabili e strumenti concreti per scalare le classifiche.</p>
    </div>
    <div>
      <h4 class="text-white font-semibold mb-3">Esplora</h4>
      <ul class="space-y-2 text-white/70">
        <li><a class="hover:text-white transition" href="<?= asset('index.php') ?>#selector">Leaderboard live</a></li>
        <li><a class="hover:text-white transition" href="<?= asset('index.php') ?>#insights">Insights &amp; analisi</a></li>
        <li><a class="hover:text-white transition" href="<?= asset('index.php') ?>#pro">RaceVerse PRO</a></li>
        <li><a class="hover:text-white transition" href="<?= asset('payment.php') ?>">Pagina abbonamento</a></li>
      </ul>
    </div>
    <div class="space-y-3">
      <h4 class="text-white font-semibold">Resta aggiornato</h4>
      <p class="text-white/60 text-sm">Iscriviti alla newsletter per ricevere setup mirati e aggiornamenti dai campionati.</p>
      <form class="flex gap-2">
        <input type="email" placeholder="La tua email" class="flex-1 px-4 py-2 rounded-xl bg-white/10 border border-white/20 placeholder:text-white/50 focus:outline-none focus:border-indigo-400">
        <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/20">Unisciti</button>
      </form>
    </div>
  </div>
  <div class="border-t border-white/10 text-xs text-white/50 py-4 text-center">© <?= date('Y') ?> RaceVerse. Tutti i diritti riservati.</div>
</footer>
</body>
</html>
