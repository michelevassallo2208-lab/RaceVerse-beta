<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$user = Auth::user();
if (!$user) { header('Location: /login.php'); exit; }
include __DIR__ . '/../templates/header.php';
?>
<section class="rounded-3xl p-6 md:p-8 bg-white/5 border border-white/10 mb-8">
  <div class="flex items-center gap-3 mb-4">
    <img src="<?= asset('assets/images/logo.png') ?>" class="w-14 h-14 drop-shadow-lg" alt="Raceverse logo">
    <div>
      <h1 class="text-2xl font-bold">Ciao, <?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?: htmlspecialchars($user['email']) ?></h1>
      <p class="text-sm text-white/60">Email: <strong><?= htmlspecialchars($user['email']) ?></strong> • Ruolo: <strong><?= htmlspecialchars($user['role']) ?></strong> • Piano: <strong><?= htmlspecialchars($user['subscription_plan'] ?: 'Nessuno') ?></strong> <?= $user['subscription_active'] ? '✅' : '❌' ?></p>
    </div>
  </div>

  <?php if (Auth::isAdmin()): ?>
    <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20">
      <div class="font-semibold mb-1">Area Amministratore</div>
      <p class="text-sm text-amber-200">Gestisci i contenuti dal <a href="/admin/index.php" class="underline">Pannello Admin</a>.</p>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="p-5 rounded-xl bg-black/40 border border-white/10">
      <h3 class="font-semibold mb-2">Raceverse Pro</h3>
      <p class="text-sm text-white/70 mb-3">Accesso al download degli assetti premium.</p>
      <?php if (!Auth::isPro()): ?>
        <a href="#" class="px-4 py-2 rounded-lg bg-white text-black inline-block">Attiva abbonamento</a>
      <?php else: ?>
        <span class="px-3 py-2 rounded-lg bg-emerald-600/30 border border-emerald-400/40 text-emerald-200 text-sm">Abbonamento attivo</span>
      <?php endif; ?>
    </div>
    <div class="p-5 rounded-xl bg-black/40 border border-white/10">
      <h3 class="font-semibold mb-2">Sessione</h3>
      <a href="/logout.php" class="px-4 py-2 rounded-lg bg-white/10 border border-white/20 inline-block">Logout</a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
