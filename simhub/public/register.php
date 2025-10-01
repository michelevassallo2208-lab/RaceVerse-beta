<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';
Auth::start();
if (Auth::user()) { header('Location: /account.php'); exit; }
include __DIR__ . '/../templates/header.php';
?>
<section class="max-w-2xl mx-auto rounded-2xl p-6 md:p-8 bg-white/5 border border-white/10">
  <div class="flex items-center gap-3 mb-6">
    <img src="/assets/images/logo.png" class="w-10 h-10" alt="logo">
    <h1 class="text-xl font-bold">Crea il tuo account</h1>
  </div>
  <p class="text-sm text-white/70 mb-4">
    La registrazione online non è ancora disponibile. Contattaci per attivare il tuo account RaceVerse Pro e sbloccare i contenuti premium.
  </p>
  <a href="mailto:info@raceverse.com" class="inline-flex items-center px-4 py-2 rounded-xl bg-white text-black font-semibold">Scrivici</a>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
