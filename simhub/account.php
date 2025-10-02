<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/helpers.php';
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
include __DIR__ . '/templates/header.php';
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
            <p class="text-sm text-white/70">Report avanzati, setup esclusivi e supporto prioritario da €2,99/mese.</p>
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
          <div class="space-y-3 text-sm text-white/70" id="account-retention">
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
            <div class="flex flex-wrap gap-3">
              <a href="<?= asset('payment.php') ?>" class="inline-flex px-5 py-2 rounded-xl bg-white/10 border border-white/15 text-white/80 hover:text-white">Dettagli abbonamento</a>
              <button type="button" data-action="open-retention" class="inline-flex px-5 py-2 rounded-xl bg-gradient-to-r from-rose-500 via-fuchsia-500 to-indigo-500 text-black font-semibold shadow-lg shadow-rose-500/30">Richiedi cancellazione</button>
            </div>
            <div data-role="retention-panel" class="p-4 rounded-2xl bg-black/50 border border-rose-400/30 text-white/80 space-y-3" style="display:none;">
              <div class="text-sm font-semibold">Prima di andare via...</div>
              <p class="text-sm">Rimani con noi e ti riserviamo un <strong>30% di sconto sul prossimo mese di RaceVerse PRO</strong>. Preferisci continuare a goderti tutti i vantaggi premium o vuoi comunque disdire?</p>
              <div class="flex flex-wrap gap-3 text-sm">
                <button type="button" data-action="stay-pro" class="px-4 py-2 rounded-xl bg-emerald-500/20 border border-emerald-300/40 text-emerald-100 hover:bg-emerald-500/30">Accetta lo sconto e rimani</button>
                <button type="button" data-action="confirm-cancel" class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Continua con la cancellazione</button>
              </div>
              <p data-role="retention-message" class="text-xs text-white/60">Nessuna azione ancora registrata.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <div class="p-6 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold text-lg mb-2">Telemetria personale</h3>
        <p class="text-sm text-white/70">Importa i tuoi giri e confrontali con i best lap. Disponibile presto con la nuova release RaceVerse Analyzer.</p>
      </div>
    </div>
    <div class="space-y-4">
      <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-3">
        <h3 class="font-semibold text-lg">Centro assistenza</h3>
        <p class="text-sm text-white/70">Apri ticket prioritari, consulta lo storico delle risposte e resta in contatto con il team RaceVerse.</p>
        <a href="<?= asset('support.php') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30">
          <span>Gestisci i tuoi ticket</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12l-3.75 3.75M21 12H3" />
          </svg>
        </a>
        <p class="text-xs text-white/50">Ti forniremo un codice ticket univoco, utile anche per seguire la richiesta via email.</p>
      </div>
      <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold text-lg mb-1">Ultimo accesso</h3>
        <p class="text-xs text-white/50"><?= date('d/m/Y H:i') ?></p>
      </div>
    </div>
  </div>
</section>
<script>
  (() => {
    const attachRetention = (rootId) => {
      const root = document.getElementById(rootId);
      if (!root) return;
      const panel = root.querySelector('[data-role="retention-panel"]');
      const trigger = root.querySelector('[data-action="open-retention"]');
      const stay = root.querySelector('[data-action="stay-pro"]');
      const cancel = root.querySelector('[data-action="confirm-cancel"]');
      const message = root.querySelector('[data-role="retention-message"]');
      if (trigger && panel) {
        trigger.addEventListener('click', () => {
          panel.style.display = 'block';
          trigger.setAttribute('aria-expanded', 'true');
        });
      }
      if (stay && message) {
        stay.addEventListener('click', () => {
          message.textContent = 'Perfetto! Ti riserviamo il 30% di sconto per il prossimo mese. Riceverai una conferma via email a breve.';
          stay.textContent = 'Sconto applicato';
          stay.setAttribute('disabled', 'disabled');
          if (cancel) {
            cancel.setAttribute('disabled', 'disabled');
          }
        });
      }
      if (cancel && message) {
        cancel.addEventListener('click', () => {
          message.textContent = 'Abbiamo registrato la tua richiesta di cancellazione. Il team RaceVerse ti contatterà per completare la procedura.';
          cancel.textContent = 'Richiesta inviata';
          cancel.setAttribute('disabled', 'disabled');
          if (stay) {
            stay.setAttribute('disabled', 'disabled');
          }
        });
      }
    };
    attachRetention('account-retention');
  })();
</script>
<?php include __DIR__ . '/templates/footer.php'; ?>
