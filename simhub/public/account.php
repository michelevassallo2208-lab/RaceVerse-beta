<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/helpers.php';
Auth::start();
$user = Auth::user();
if (!$user) { redirect_to('login.php'); }
include __DIR__ . '/../templates/header.php';
?>
<section class="rounded-3xl p-8 bg-black/40 border border-white/10 shadow-2xl shadow-emerald-500/20">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
    <div class="flex items-center gap-4">
      <div class="relative">
        <span class="absolute inset-0 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-emerald-500 blur"></span>
        <img src="<?= asset('assets/images/logo.png') ?>" class="relative w-14 h-14" alt="logo">
      </div>
      <div>
        <h1 class="text-2xl font-bold">Ciao, <?= htmlspecialchars($user['email']) ?></h1>
        <p class="text-sm text-white/60">Ruolo: <strong><?= htmlspecialchars($user['role']) ?></strong> • Piano: <strong><?= htmlspecialchars($user['subscription_plan'] ?: 'Nessuno') ?></strong> <?= $user['subscription_active'] ? '✅' : '❌' ?></p>
      </div>
    </div>
    <div class="flex gap-3">
      <a href="<?= asset('index.php') ?>#selector" class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Vai ai dati</a>
      <a href="<?= asset('logout.php') ?>" class="px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 via-fuchsia-500 to-indigo-500 text-black font-semibold shadow-lg shadow-fuchsia-500/30">Logout</a>
    </div>
  </div>

  <?php if (Auth::isAdmin()): ?>
    <div class="mb-8 p-5 rounded-2xl bg-amber-500/15 border border-amber-400/30 text-amber-100">
      <div class="font-semibold mb-1">Area Amministratore</div>
      <p class="text-sm">Gestisci i contenuti dal <a href="<?= asset('../admin/index.php') ?>" class="underline">Pannello Admin</a>.</p>
    </div>
  <?php endif; ?>

  <div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2 space-y-4">
      <div class="p-6 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold text-lg mb-2">RaceVerse PRO</h3>
        <p class="text-sm text-white/70 mb-4">Salva setup, monitora i tuoi riferimenti e ricevi aggiornamenti operativi per preparare ogni weekend.</p>
        <?php if (!Auth::isPro()): ?>
          <a href="<?= asset('register.php') ?>" class="inline-flex px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Attiva abbonamento</a>
        <?php else: ?>
          <span class="inline-flex px-4 py-2 rounded-xl bg-emerald-600/25 border border-emerald-400/40 text-emerald-200 text-sm">Abbonamento attivo</span>
        <?php endif; ?>
      </div>
      <div class="p-6 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold text-lg mb-2">Telemetria personale</h3>
        <p class="text-sm text-white/70">Importa i tuoi giri e confrontali con i best lap. Disponibile presto con la nuova release RaceVerse Analyzer.</p>
      </div>
    </div>
    <div class="space-y-4">
      <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold text-lg mb-2">Supporto</h3>
        <p class="text-sm text-white/70 mb-3">Contatta il nostro team per richieste di setup o coaching personalizzato.</p>
        <a href="mailto:support@raceverse.gg" class="inline-flex px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Scrivi al supporto</a>
      </div>
      <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold text-lg mb-1">Ultimo accesso</h3>
        <p class="text-xs text-white/50"><?= date('d/m/Y H:i') ?></p>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
