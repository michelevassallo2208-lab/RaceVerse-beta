<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/helpers.php';
Auth::start();
$user = Auth::user();
if (!$user) { redirect_to('login.php'); }
$pdo = null;
try {
  $pdo = Database::pdo();
} catch (PDOException $e) {
  $pdo = null;
}
$fresh = null;
if ($pdo) {
  try {
    $st = $pdo->prepare('SELECT subscription_plan, subscription_active, subscription_started_at, subscription_renews_at, subscription_payment_method, subscription_cancel_at_period_end FROM users WHERE id = ? LIMIT 1');
    $st->execute([$user['id']]);
    $fresh = $st->fetch();
  } catch (PDOException $e) {
    $fresh = null;
  }
}
if ($fresh) {
  $user = array_merge($user, $fresh);
}
$formatDateTime = static function (?string $dateTime): ?string {
  if (!$dateTime) { return null; }
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
  return $dt ? $dt->format('d/m/Y H:i') : $dateTime;
};
$isPro = Auth::isPro();
$planName = $user['subscription_plan'] ?: 'RaceVerse BASIC';
$startedAt = $formatDateTime($user['subscription_started_at'] ?? null);
$renewsAt = $formatDateTime($user['subscription_renews_at'] ?? null);
$paymentMethod = $user['subscription_payment_method'] ?? null;
$cancelAtPeriodEnd = !empty($user['subscription_cancel_at_period_end']);
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
        <p class="text-sm text-white/60">Ruolo: <strong><?= htmlspecialchars($user['role']) ?></strong> • Piano: <strong><?= htmlspecialchars($planName) ?></strong> <?= $isPro ? '✅' : '⌛' ?></p>
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
      <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-4">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="font-semibold text-lg mb-1">RaceVerse PRO</h3>
            <p class="text-sm text-white/70">Report avanzati, setup esclusivi e supporto prioritario a €2,99/mese.</p>
          </div>
          <?php if ($isPro): ?>
            <span class="inline-flex px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200 text-xs uppercase tracking-widest">Attivo</span>
          <?php endif; ?>
        </div>
        <?php if (!$isPro): ?>
          <div class="flex flex-wrap gap-3">
            <a href="<?= asset('payment.php') ?>" class="inline-flex px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Passa a RaceVerse PRO</a>
            <a href="<?= asset('payment.php') ?>" class="inline-flex px-5 py-2 rounded-xl bg-white/10 border border-white/15 text-white/80 hover:text-white">Scopri vantaggi</a>
          </div>
        <?php else: ?>
          <div class="space-y-3 text-sm text-white/70">
            <p class="font-semibold text-white flex items-center gap-2"><span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">✓</span> Sei già abbonato a RaceVerse PRO.</p>
            <dl class="grid sm:grid-cols-2 gap-3 text-xs sm:text-sm">
              <div class="p-3 rounded-xl bg-black/30 border border-white/10">
                <dt class="text-white/60 uppercase tracking-wide text-[0.65rem] mb-1">Attivo dal</dt>
                <dd class="text-white/90 font-medium"><?= htmlspecialchars($startedAt ?? '—') ?></dd>
              </div>
              <div class="p-3 rounded-xl bg-black/30 border border-white/10">
                <dt class="text-white/60 uppercase tracking-wide text-[0.65rem] mb-1">Rinnovo</dt>
                <dd class="text-white/90 font-medium"><?= htmlspecialchars($renewsAt ?? '—') ?></dd>
              </div>
              <div class="p-3 rounded-xl bg-black/30 border border-white/10">
                <dt class="text-white/60 uppercase tracking-wide text-[0.65rem] mb-1">Metodo di pagamento</dt>
                <dd class="text-white/90 font-medium"><?= htmlspecialchars($paymentMethod ?? 'In aggiornamento') ?></dd>
              </div>
              <div class="p-3 rounded-xl bg-black/30 border border-white/10">
                <dt class="text-white/60 uppercase tracking-wide text-[0.65rem] mb-1">Cancellazione</dt>
                <dd class="text-white/90 font-medium"><?= $cancelAtPeriodEnd ? 'Disdetto: termina a fine periodo' : 'Puoi disdire: terminerà a fine periodo pagato' ?></dd>
              </div>
            </dl>
            <a href="<?= asset('payment.php') ?>" class="inline-flex px-5 py-2 rounded-xl bg-white/10 border border-white/15 text-white/80 hover:text-white">Dettagli abbonamento</a>
          </div>
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
