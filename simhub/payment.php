<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/SubscriptionManager.php';
Auth::start();
$currentUser = Auth::user();
$pdo = null;
try {
  $pdo = Database::pdo();
} catch (PDOException $e) {
  $pdo = null;
}
$subscription = [];
  if ($currentUser && $pdo) {
  try {
    $st = $pdo->prepare('SELECT subscription_plan, subscription_active, subscription_started_at, subscription_expires_at, subscription_payment_method FROM users WHERE id = ? LIMIT 1');
    $st->execute([$currentUser['id']]);
    $subscription = $st->fetch() ?: [];
  } catch (PDOException $e) {
    $subscription = [];
  }
}
$userSnapshot = $currentUser;
if ($subscription) {
  $userSnapshot = array_merge($userSnapshot ?? [], $subscription);
}
if ($userSnapshot) {
  $userSnapshot = SubscriptionManager::normalizeUser($userSnapshot);
}
$planName = $userSnapshot['subscription_plan'] ?? 'RaceVerse BASIC';
$subscriptionActive = !empty($userSnapshot['subscription_active']);
$isPro = $subscriptionActive && strpos($planName, 'RaceVerse PRO') !== false;
$formatDateTime = static function (?string $dateTime): ?string {
  if (!$dateTime) { return null; }
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
  return $dt ? $dt->format('d/m/Y H:i') : $dateTime;
};
$startedAt = $formatDateTime($userSnapshot['subscription_started_at'] ?? null);
$expiresAt = $formatDateTime($userSnapshot['subscription_expires_at'] ?? null);
$paymentMethod = $userSnapshot['subscription_payment_method'] ?? null;
$plans = SubscriptionManager::allPlans();
$status = $_GET['status'] ?? null;
$statusMessage = null;
if ($status === 'success') {
  $statusMessage = 'Pagamento completato con successo! Il tuo accesso RaceVerse PRO è ora attivo.';
} elseif ($status === 'cancelled') {
  $statusMessage = 'Hai annullato il pagamento. Puoi riprovare quando vuoi.';
} elseif ($status === 'error') {
  $statusMessage = 'Si è verificato un errore durante la procedura di pagamento. Riprova o contatta il supporto.';
}
include __DIR__ . '/templates/header.php';
?>

<?php if ($isPro): ?>
  <section class="rounded-3xl p-8 md:p-12 bg-gradient-to-br from-emerald-700/30 via-teal-600/20 to-indigo-700/30 border border-white/10 shadow-2xl shadow-emerald-500/20">
    <div class="max-w-4xl space-y-5">
      <span class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/10 border border-white/20 text-xs uppercase tracking-[0.35em]">Pass PRO attivo</span>
      <h1 class="text-4xl md:text-5xl font-black leading-tight">Hai già attivato l'accesso RaceVerse PRO</h1>
      <?php if ($statusMessage): ?>
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10 text-sm text-white/80"><?= htmlspecialchars($statusMessage) ?></div>
      <?php endif; ?>
      <p class="text-white/70 text-lg">Grazie per supportare RaceVerse: il tuo pass PRO ti garantisce accesso prioritario a setup, report avanzati e assistenza dedicata fino alla scadenza indicata.</p>
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10">
          <div class="text-white/50 text-xs uppercase tracking-wider mb-1">Tipo accesso</div>
          <div class="text-white font-semibold"><?= htmlspecialchars($planName) ?></div>
        </div>
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10">
          <div class="text-white/50 text-xs uppercase tracking-wider mb-1">Attivo dal</div>
          <div class="text-white font-semibold"><?= htmlspecialchars($startedAt ?? '—') ?></div>
        </div>
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10">
          <div class="text-white/50 text-xs uppercase tracking-wider mb-1">Scadenza</div>
          <div class="text-white font-semibold"><?= htmlspecialchars($expiresAt ?? '—') ?></div>
        </div>
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10">
          <div class="text-white/50 text-xs uppercase tracking-wider mb-1">Pagamento</div>
          <div class="text-white font-semibold"><?= htmlspecialchars($paymentMethod ?? 'In aggiornamento') ?></div>
        </div>
      </div>
    </div>
  </section>

  <section class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-emerald-500/10">
        <h2 class="text-2xl font-bold mb-4">Dettagli del tuo accesso</h2>
        <ul class="space-y-3 text-sm text-white/75">
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">1</span> Accesso immediato ai setup certificati e alle analisi dei migliori hotlap.</li>
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">2</span> Supporto prioritario via email con il team RaceVerse per coaching e richieste dedicate.</li>
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">3</span> Nessun rinnovo automatico: al termine del periodo potrai acquistare un nuovo pass quando preferisci.</li>
        </ul>
      </div>

      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-indigo-500/10">
        <h2 class="text-2xl font-bold mb-4">Gestione accesso</h2>
        <p class="text-sm text-white/70 mb-4">Non sono previsti rinnovi automatici. Alla scadenza potrai acquistare un nuovo pass direttamente da questa pagina oppure contattare il supporto per eventuali proroghe personalizzate.</p>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-white/75">
          <p class="mb-2"><strong>Scadenza attuale:</strong> <?= htmlspecialchars($expiresAt ?? '—') ?></p>
          <p>Hai bisogno di estendere il pass o di una fattura? Scrivi a <a href="mailto:support@raceverse.it" class="underline">support@raceverse.it</a>.</p>
        </div>
        <div class="mt-4 flex flex-wrap gap-3">
          <a href="<?= asset('payment.php') ?>" class="inline-flex px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Acquista un nuovo pass</a>
          <a href="mailto:support@raceverse.it?subject=Assistenza%20pass%20RaceVerse%20PRO" class="inline-flex px-5 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Supporto dedicato</a>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-500/30 via-teal-500/20 to-cyan-500/30 border border-emerald-400/40 shadow-xl shadow-emerald-500/30 text-sm text-white/80">
        <h3 class="text-lg font-semibold text-white mb-3">Prossimi step</h3>
        <ul class="space-y-2">
          <li>• Continua a consultare i setup dalla dashboard.</li>
          <li>• Riceverai email dedicate con nuovi pacchetti PRO.</li>
          <li>• Puoi chiedere analisi personalizzate scrivendoci direttamente.</li>
        </ul>
      </div>

      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-purple-500/10 text-sm text-white/70">
        <h3 class="text-lg font-semibold text-white mb-3">Hai bisogno di aiuto?</h3>
        <p class="mb-3">Il nostro team risponde rapidamente a chi possiede un pass PRO. Prepariamo insieme la strategia per il tuo prossimo weekend di gara.</p>
        <a href="mailto:support@raceverse.it" class="inline-flex px-5 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Contatta il supporto</a>
      </div>
    </div>
  </section>
<?php else: ?>
  <section class="rounded-3xl p-8 md:p-12 bg-gradient-to-br from-emerald-600/20 via-cyan-500/10 to-indigo-600/20 border border-white/10 shadow-2xl shadow-emerald-500/20">
    <div class="max-w-3xl space-y-5">
      <span class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/10 border border-white/20 text-xs uppercase tracking-[0.35em]">Upgrade PRO</span>
      <h1 class="text-4xl md:text-5xl font-black leading-tight">Accesso RaceVerse PRO da €2,99</h1>
      <p class="text-white/70 text-lg">Potenzia il tuo account BASIC con report avanzati, setup certificati e supporto prioritario. Scegli un pass occasionale (30, 90, 180 o 365 giorni) e attivalo subito con Stripe o PayPal.</p>
      <?php if ($statusMessage): ?>
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10 text-sm text-white/80"><?= htmlspecialchars($statusMessage) ?></div>
      <?php endif; ?>
      <div class="flex flex-wrap gap-3">
        <?php if (!$currentUser): ?>
          <a href="<?= asset('register.php') ?>" class="px-6 py-3 rounded-2xl bg-white text-black font-semibold shadow-lg shadow-white/40">Registrati gratis</a>
        <?php else: ?>
          <a href="#piani" class="px-6 py-3 rounded-2xl bg-white text-black font-semibold shadow-lg shadow-white/40">Scegli il tuo piano</a>
        <?php endif; ?>
        <a href="mailto:support@raceverse.it?subject=Domande%20pass%20RaceVerse%20PRO" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/30 text-white/80 hover:text-white">Serve assistenza?</a>
      </div>
    </div>
  </section>

  <section class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-emerald-500/10">
        <h2 class="text-2xl font-bold mb-4">Cosa include l'accesso RaceVerse PRO</h2>
        <div class="grid md:grid-cols-2 gap-4 text-sm text-white/75">
          <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
            <div class="text-lg font-semibold text-white mb-2">Report dinamici</div>
            <p>Analisi comparative per categoria, pista e pilota con dati aggiornati dal tuo database.</p>
          </div>
          <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
            <div class="text-lg font-semibold text-white mb-2">Setup certificati</div>
            <p>Accesso ai setup sviluppati dal team RaceVerse per endurance e sprint.</p>
          </div>
          <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
            <div class="text-lg font-semibold text-white mb-2">Storico personale</div>
            <p>Tracking automatico dei tuoi hotlap con note, allegati e progresso nel tempo.</p>
          </div>
          <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
            <div class="text-lg font-semibold text-white mb-2">Supporto priority</div>
            <p>Linea diretta via email con risposte rapide del team per coaching e richieste setup.</p>
          </div>
        </div>
      </div>

      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-indigo-500/10">
        <h2 class="text-2xl font-bold mb-4">Come funziona l'attivazione</h2>
        <ol class="space-y-3 text-white/70 text-sm">
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">1</span> Confermi l'account BASIC e scegli il pass PRO che preferisci.</li>
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">2</span> Completi il checkout protetto con Stripe o PayPal.</li>
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">3</span> Torni su RaceVerse e trovi attivo l'accesso per tutta la durata acquistata.</li>
        </ol>
        <p class="mt-4 text-xs text-white/50">Nessun rinnovo automatico: alla scadenza potrai acquistare un nuovo pass quando desideri.</p>
      </div>
    </div>

    <div class="space-y-6">
      <div id="piani" class="p-6 rounded-3xl bg-gradient-to-br from-emerald-500/30 via-teal-500/20 to-cyan-500/30 border border-emerald-400/40 shadow-xl shadow-emerald-500/30">
        <div class="text-xs uppercase tracking-[0.4em] text-white/70 mb-2">Scegli il tuo pass PRO</div>
        <div class="grid gap-4 text-sm">
          <?php foreach ($plans as $plan): ?>
            <div class="p-4 rounded-2xl bg-black/40 border border-white/10 space-y-4">
              <div class="flex items-baseline justify-between gap-2">
                <div>
                  <div class="text-white font-semibold text-lg"><?= htmlspecialchars($plan['label']) ?> di accesso</div>
                  <div class="text-white/60 text-xs uppercase tracking-widest">Pass RaceVerse PRO</div>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-black text-white">€<?= number_format($plan['price_eur'], 2, ',', '.') ?></div>
                  <div class="text-white/60 text-xs">una tantum</div>
                </div>
              </div>
              <p class="text-white/70 text-sm">Attiva subito <?= strtolower($plan['label']) ?> di vantaggi premium: setup certificati, analytics avanzate e supporto priority senza rinnovi automatici.</p>
              <?php if ($currentUser): ?>
                <div class="grid sm:grid-cols-2 gap-3">
                  <form method="post" action="<?= asset('payment-start.php') ?>" class="contents">
                    <input type="hidden" name="plan" value="<?= htmlspecialchars($plan['code']) ?>">
                    <input type="hidden" name="provider" value="stripe">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Paga con Stripe</button>
                  </form>
                  <form method="post" action="<?= asset('payment-start.php') ?>" class="contents">
                    <input type="hidden" name="plan" value="<?= htmlspecialchars($plan['code']) ?>">
                    <input type="hidden" name="provider" value="paypal">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Paga con PayPal</button>
                  </form>
                </div>
              <?php else: ?>
                <a href="<?= asset('login.php') ?>" class="inline-flex px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Accedi per acquistare</a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <ul class="mt-4 space-y-2 text-sm text-white/80">
          <li>• RaceVerse BASIC resta gratuito con conferma email immediata.</li>
          <li>• Seleziona la durata che preferisci: 1, 3, 6 o 12 mesi di accesso.</li>
          <li>• Nessun rinnovo automatico: al termine potrai acquistare un nuovo pass.</li>
        </ul>
      </div>

      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-purple-500/10 text-sm text-white/70">
        <h3 class="text-lg font-semibold text-white mb-3">Domande frequenti</h3>
        <p class="mb-3"><strong>Il pagamento è ricorrente?</strong><br>No, ogni pass è un acquisto singolo. Alla scadenza potrai comprare un nuovo accesso quando ti serve.</p>
        <p class="mb-3"><strong>Quando si attiva il pass?</strong><br>Subito dopo il pagamento riceverai conferma via email e l'accesso PRO sarà valido per l'intero periodo scelto.</p>
        <p><strong>Posso avere fattura?</strong><br>Sì, contattaci su <a href="mailto:billing@raceverse.it" class="underline">billing@raceverse.it</a> indicando numero ordine e dati fiscali.</p>
      </div>
    </div>
  </section>
<?php endif; ?>
<?php include __DIR__ . '/templates/footer.php'; ?>
