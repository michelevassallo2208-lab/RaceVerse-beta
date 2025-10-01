<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/helpers.php';
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
    $st = $pdo->prepare('SELECT subscription_plan, subscription_active, subscription_started_at, subscription_renews_at, subscription_payment_method, subscription_cancel_at_period_end FROM users WHERE id = ? LIMIT 1');
    $st->execute([$currentUser['id']]);
    $subscription = $st->fetch() ?: [];
  } catch (PDOException $e) {
    $subscription = [];
  }
}
$planName = $subscription['subscription_plan'] ?? ($currentUser['subscription_plan'] ?? 'RaceVerse BASIC');
$subscriptionActive = !empty($subscription['subscription_active']);
$isPro = $subscriptionActive && $planName === 'RaceVerse PRO';
$formatDateTime = static function (?string $dateTime): ?string {
  if (!$dateTime) { return null; }
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
  return $dt ? $dt->format('d/m/Y H:i') : $dateTime;
};
$startedAt = $formatDateTime($subscription['subscription_started_at'] ?? null);
$renewsAt = $formatDateTime($subscription['subscription_renews_at'] ?? null);
$paymentMethod = $subscription['subscription_payment_method'] ?? null;
$cancelAtPeriodEnd = !empty($subscription['subscription_cancel_at_period_end']);
include __DIR__ . '/../templates/header.php';
?>

<?php if ($isPro): ?>
  <section class="rounded-3xl p-8 md:p-12 bg-gradient-to-br from-emerald-700/30 via-teal-600/20 to-indigo-700/30 border border-white/10 shadow-2xl shadow-emerald-500/20">
    <div class="max-w-4xl space-y-5">
      <span class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/10 border border-white/20 text-xs uppercase tracking-[0.35em]">Abbonamento attivo</span>
      <h1 class="text-4xl md:text-5xl font-black leading-tight">Sei già abbonato a RaceVerse PRO</h1>
      <p class="text-white/70 text-lg">Grazie per supportare RaceVerse: il tuo piano PRO è attivo e continuerà a offrirti accesso prioritario a setup, report avanzati e assistenza dedicata.</p>
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10">
          <div class="text-white/50 text-xs uppercase tracking-wider mb-1">Piano</div>
          <div class="text-white font-semibold">RaceVerse PRO</div>
        </div>
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10">
          <div class="text-white/50 text-xs uppercase tracking-wider mb-1">Attivo dal</div>
          <div class="text-white font-semibold"><?= htmlspecialchars($startedAt ?? '—') ?></div>
        </div>
        <div class="p-4 rounded-2xl bg-black/40 border border-white/10">
          <div class="text-white/50 text-xs uppercase tracking-wider mb-1">Prossimo rinnovo</div>
          <div class="text-white font-semibold"><?= htmlspecialchars($renewsAt ?? '—') ?></div>
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
        <h2 class="text-2xl font-bold mb-4">Dettagli del tuo abbonamento</h2>
        <ul class="space-y-3 text-sm text-white/75">
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">1</span> Accesso immediato ai setup certificati e alle analisi dei migliori hotlap.</li>
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">2</span> Supporto prioritario via email con il team RaceVerse per coaching e richieste dedicate.</li>
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">3</span> Nuovi contenuti premium in arrivo: riceverai notifiche con ogni aggiornamento.</li>
        </ul>
      </div>

      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-indigo-500/10" id="payment-retention">
        <h2 class="text-2xl font-bold mb-4">Gestione e cancellazione</h2>
        <p class="text-sm text-white/70 mb-4">Puoi richiedere modifiche o disdetta in qualsiasi momento. La cancellazione diventa effettiva alla fine del periodo già pagato, così non perdi nessuno dei vantaggi attivi.</p>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-white/75">
          <p class="mb-2"><strong>Stato cancellazione:</strong> <?= $cancelAtPeriodEnd ? 'in corso — il piano resterà attivo fino al termine del ciclo attuale.' : 'non richiesto — il piano si rinnoverà automaticamente ogni mese.' ?></p>
          <p>Per aggiornare il metodo di pagamento o richiedere assistenza scrivi a <a href="mailto:support@raceverse.it" class="underline">support@raceverse.it</a>.</p>
        </div>
        <div class="mt-4 flex flex-wrap gap-3">
          <button type="button" data-action="open-retention" class="inline-flex px-5 py-2 rounded-xl bg-gradient-to-r from-rose-500 via-fuchsia-500 to-indigo-500 text-black font-semibold shadow-lg shadow-rose-500/30">Richiedi cancellazione</button>
          <a href="mailto:support@raceverse.it?subject=Aggiornamento%20pagamento%20RaceVerse%20PRO" class="inline-flex px-5 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Supporto dedicato</a>
        </div>
        <div data-role="retention-panel" class="mt-4 p-4 rounded-2xl bg-black/50 border border-rose-400/30 text-white/80 space-y-3" style="display:none;">
          <div class="text-sm font-semibold">Possiamo offrirti qualcosa in più</div>
          <p class="text-sm">Se resti con RaceVerse PRO ti accreditiamo automaticamente un <strong>30% di sconto sul prossimo rinnovo</strong>. Preferisci approfittarne o vuoi proseguire con la disdetta?</p>
          <div class="flex flex-wrap gap-3 text-sm">
            <button type="button" data-action="stay-pro" class="px-4 py-2 rounded-xl bg-emerald-500/20 border border-emerald-300/40 text-emerald-100 hover:bg-emerald-500/30">Accetto lo sconto</button>
            <button type="button" data-action="confirm-cancel" class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Continua con la cancellazione</button>
          </div>
          <p data-role="retention-message" class="text-xs text-white/60">Scegli un'opzione per procedere.</p>
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
        <p class="mb-3">Il nostro team risponde rapidamente agli abbonati PRO. Prepariamo insieme la strategia per il tuo prossimo weekend di gara.</p>
        <a href="mailto:support@raceverse.it" class="inline-flex px-5 py-2 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Contatta il supporto</a>
      </div>
    </div>
  </section>
<?php else: ?>
  <section class="rounded-3xl p-8 md:p-12 bg-gradient-to-br from-emerald-600/20 via-cyan-500/10 to-indigo-600/20 border border-white/10 shadow-2xl shadow-emerald-500/20">
    <div class="max-w-3xl space-y-5">
      <span class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/10 border border-white/20 text-xs uppercase tracking-[0.35em]">Upgrade PRO</span>
      <h1 class="text-4xl md:text-5xl font-black leading-tight">RaceVerse PRO da €2,99/mese</h1>
      <p class="text-white/70 text-lg">Potenzia il tuo account BASIC con report avanzati, setup certificati e supporto prioritario. Il checkout online è in arrivo: nel frattempo puoi pre-registrarti e ricevere assistenza per l'attivazione.</p>
      <div class="flex flex-wrap gap-3">
        <?php if (!$currentUser): ?>
          <a href="<?= asset('register.php') ?>" class="px-6 py-3 rounded-2xl bg-white text-black font-semibold shadow-lg shadow-white/40">Registrati gratis</a>
        <?php else: ?>
          <a href="mailto:support@raceverse.it?subject=Attivazione%20RaceVerse%20PRO" class="px-6 py-3 rounded-2xl bg-white text-black font-semibold shadow-lg shadow-white/40">Richiedi attivazione assistita</a>
        <?php endif; ?>
        <button class="px-6 py-3 rounded-2xl bg-white/10 border border-white/30 text-white/80 cursor-not-allowed" disabled>Pagamento online in arrivo</button>
      </div>
    </div>
  </section>

  <section class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-emerald-500/10">
        <h2 class="text-2xl font-bold mb-4">Cosa include RaceVerse PRO</h2>
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
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">1</span> Confermi l'account BASIC e richiedi l'upgrade PRO dal pulsante qui sopra.</li>
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">2</span> Ricevi il link di pagamento sicuro (Stripe) da €2,99/mese.</li>
          <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">3</span> Dopo il pagamento, il tuo profilo viene aggiornato a RaceVerse PRO e sblocca i contenuti premium.</li>
        </ol>
        <p class="mt-4 text-xs text-white/50">Puoi annullare l'abbonamento in qualsiasi momento: l'accesso resterà attivo fino alla fine del periodo già pagato.</p>
      </div>
    </div>

    <div class="space-y-6">
      <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-500/30 via-teal-500/20 to-cyan-500/30 border border-emerald-400/40 shadow-xl shadow-emerald-500/30">
        <div class="text-xs uppercase tracking-[0.4em] text-white/70 mb-2">Scegli il tuo piano PRO</div>
        <div class="grid gap-4 text-sm">
          <div class="p-4 rounded-2xl bg-black/40 border border-white/10">
            <div class="flex items-baseline justify-between gap-2">
              <div>
                <div class="text-white font-semibold text-lg">Mensile</div>
                <div class="text-white/60 text-xs uppercase tracking-widest">Flessibile</div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-black text-white">€2,99</div>
                <div class="text-white/60 text-xs">al mese</div>
              </div>
            </div>
            <p class="mt-3 text-white/70">Per chi vuole testare tutti i contenuti premium senza vincoli.</p>
          </div>
          <div class="p-4 rounded-2xl bg-black/40 border border-emerald-300/40">
            <div class="flex items-baseline justify-between gap-2">
              <div>
                <div class="text-white font-semibold text-lg">Trimestrale</div>
                <div class="text-emerald-200 text-xs uppercase tracking-widest">Risparmi €1</div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-black text-white">€7,97</div>
                <div class="text-white/60 text-xs">ogni 3 mesi</div>
              </div>
            </div>
            <p class="mt-3 text-white/70">Sconto immediato rispetto al mensile e accesso continuo alle feature PRO.</p>
          </div>
          <div class="p-4 rounded-2xl bg-black/40 border border-cyan-300/40">
            <div class="flex items-baseline justify-between gap-2">
              <div>
                <div class="text-white font-semibold text-lg">Semestrale</div>
                <div class="text-cyan-200 text-xs uppercase tracking-widest">Solo €15</div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-black text-white">€15</div>
                <div class="text-white/60 text-xs">ogni 6 mesi</div>
              </div>
            </div>
            <p class="mt-3 text-white/70">Il piano più richiesto per preparare l'intera stagione con un unico rinnovo.</p>
          </div>
          <div class="p-4 rounded-2xl bg-black/40 border border-indigo-300/40">
            <div class="flex items-baseline justify-between gap-2">
              <div>
                <div class="text-white font-semibold text-lg">Annuale</div>
                <div class="text-indigo-200 text-xs uppercase tracking-widest">Paghi €25 invece di €36</div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-black text-white">€25</div>
                <div class="text-white/60 text-xs">all'anno</div>
              </div>
            </div>
            <p class="mt-3 text-white/70">Massimo risparmio: blocchi il prezzo di lancio e ottieni l'intero ecosistema PRO.</p>
          </div>
        </div>
        <ul class="mt-4 space-y-2 text-sm text-white/80">
          <li>• RaceVerse BASIC resta gratuito con conferma email immediata.</li>
          <li>• Scegli tu la durata del piano PRO: mensile, trimestrale, semestrale o annuale.</li>
          <li>• Puoi disdire quando vuoi: la disdetta vale dal termine del periodo pagato.</li>
        </ul>
      </div>

      <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-purple-500/10 text-sm text-white/70">
        <h3 class="text-lg font-semibold text-white mb-3">Domande frequenti</h3>
        <p class="mb-3"><strong>Quando sarà attivo il checkout?</strong><br>Stiamo integrando Stripe: riceverai un avviso appena il pagamento self-service sarà disponibile.</p>
        <p class="mb-3"><strong>Posso usare PRO senza pagare ora?</strong><br>Sì, puoi richiedere l'attivazione manuale mentre finalizziamo la parte di pagamento.</p>
        <p><strong>Il prezzo aumenterà?</strong><br>No, l'offerta di lancio a €2,99/mese resta bloccata per tutti gli abbonati attivi.</p>
      </div>
    </div>
  </section>
<?php endif; ?>
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
          message.textContent = 'Perfetto! Abbiamo riservato il 30% di sconto sul prossimo rinnovo. Riceverai conferma via email.';
          stay.textContent = 'Sconto applicato';
          stay.setAttribute('disabled', 'disabled');
          if (cancel) {
            cancel.setAttribute('disabled', 'disabled');
          }
        });
      }
      if (cancel && message) {
        cancel.addEventListener('click', () => {
          message.textContent = 'La richiesta di cancellazione è stata registrata. Ti contatteremo per completare la procedura.';
          cancel.textContent = 'Richiesta inviata';
          cancel.setAttribute('disabled', 'disabled');
          if (stay) {
            stay.setAttribute('disabled', 'disabled');
          }
        });
      }
    };
    attachRetention('payment-retention');
  })();
</script>
<?php include __DIR__ . '/../templates/footer.php'; ?>
