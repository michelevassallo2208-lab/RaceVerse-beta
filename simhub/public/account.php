<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$user = Auth::user();
if (!$user) { header('Location: /login.php'); exit; }
$roleLabel = Auth::roleLabel($user['role']);
$hasSetupAccess = Auth::hasSetupAccess();
include __DIR__ . '/../templates/header.php';
?>
<section class="space-y-8">
  <div class="rounded-3xl p-8 md:p-10 bg-gradient-to-br from-indigo-500/20 via-blue-500/10 to-emerald-500/10 border border-white/10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
      <div class="flex items-start gap-4">
        <img src="/assets/images/logo.png" class="w-14 h-14" alt="logo">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-white/60 mb-2">RaceVerse Control Room</p>
          <h1 class="text-3xl md:text-4xl font-black leading-tight">Benvenuto, <?= htmlspecialchars($user['email']) ?></h1>
          <p class="text-white/70 mt-2">Ruolo corrente: <span class="font-semibold text-white"><?= htmlspecialchars($roleLabel) ?></span> • Piano: <span class="font-semibold text-white"><?= htmlspecialchars($user['subscription_plan'] ?: 'Nessuno') ?></span> <?= $user['subscription_active'] ? '✅' : '❌' ?></p>
        </div>
      </div>
      <div class="flex flex-col gap-3 min-w-[220px]">
        <a href="/logout.php" class="px-5 py-3 rounded-2xl bg-white/10 border border-white/20 text-sm text-center hover:bg-white/20">Esci dalla sessione</a>
        <?php if (!$hasSetupAccess): ?>
          <a href="#" class="px-5 py-3 rounded-2xl bg-emerald-400 text-black text-sm font-semibold text-center">Attiva RaceVerse Pro</a>
        <?php else: ?>
          <span class="px-5 py-3 rounded-2xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 text-center text-sm">Accesso setup premium attivo</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="rounded-3xl border border-white/10 bg-white/5 p-6 md:p-8">
    <div class="flex flex-wrap items-center gap-3 border-b border-white/10 pb-4">
      <button type="button" data-tab-button data-tab-target="overview" class="px-4 py-2 rounded-2xl text-sm font-semibold transition bg-white text-black shadow-lg">Dashboard</button>
      <button type="button" data-tab-button data-tab-target="subscription" class="px-4 py-2 rounded-2xl text-sm font-semibold transition bg-white/10 border border-white/20 text-white/80 hover:bg-white/15">Subscription</button>
    </div>

    <div class="mt-6 space-y-6" data-tab-panel="overview">
      <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-emerald-400/30 bg-emerald-500/10 p-6">
          <div class="text-xs uppercase tracking-[0.25em] text-emerald-200">Insight meta</div>
          <h2 class="text-xl font-semibold mt-3">Hotlap e consigli</h2>
          <p class="text-sm text-emerald-100/80 mt-2">Accedi al database delle combinazioni pista/auto aggiornato ogni settimana dai nostri pro-driver.</p>
          <ul class="mt-4 space-y-2 text-sm text-emerald-100/70">
            <li>• Analisi cross-game (LMU, iRacing, ACC)</li>
            <li>• Notifiche meta quando cambia l'auto dominante</li>
            <li>• Preferiti personali per salvare i tuoi combo</li>
          </ul>
        </div>
        <div class="rounded-3xl border border-white/10 bg-black/40 p-6">
          <div class="text-xs uppercase tracking-[0.25em] text-white/60">Setup Lab</div>
          <h2 class="text-xl font-semibold mt-3">Download assetti</h2>
          <?php if ($hasSetupAccess): ?>
            <p class="text-sm text-white/80 mt-2">Hai accesso a tutti i file assetto caricati dal team RaceVerse. Scarica la tua prossima configurazione vincente.</p>
            <ul class="mt-4 space-y-2 text-sm text-white/70">
              <li>• Telemetria MoTeC inclusa</li>
              <li>• Setup per qualifica e gara</li>
              <li>• Aggiornamenti gratuiti per l'intero mese</li>
            </ul>
            <a href="<?= asset('setups.php') ?>" class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-400 text-black text-sm font-semibold">Vai ai setup</a>
          <?php else: ?>
            <p class="text-sm text-white/70 mt-2">Per scaricare i setup è necessario un piano RaceVerse Pro attivo. Scegli tra abbonamento mensile o singolo acquisto.</p>
            <div class="mt-4 grid gap-3 text-sm">
              <div class="p-3 rounded-2xl border border-white/15 bg-white/5">3,99€/mese • Accesso illimitato a tutti gli assetti</div>
              <div class="p-3 rounded-2xl border border-white/15 bg-white/5">1,99€ • Acquisto singolo assetto</div>
            </div>
            <a href="#subscription" data-tab-jump="subscription" class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white text-black text-sm font-semibold">Diventa RaceVerse Pro</a>
          <?php endif; ?>
        </div>
        <div class="rounded-3xl border border-amber-400/30 bg-amber-500/10 p-6">
          <div class="text-xs uppercase tracking-[0.25em] text-amber-200">Roadmap</div>
          <h2 class="text-xl font-semibold mt-3">Prossimi rilasci</h2>
          <ul class="mt-4 space-y-3 text-sm text-amber-100/80">
            <li>• Dashboard strategie gomme per gare endurance</li>
            <li>• Coaching 1-to-1 con i nostri pro-driver</li>
            <li>• Integrazione live con i rating iRacing</li>
          </ul>
          <p class="text-xs text-amber-100/70 mt-4">Suggerisci nuove feature direttamente dal canale Discord riservato ai membri.</p>
        </div>
      </div>

      <?php if (Auth::isAdmin()): ?>
        <section class="rounded-3xl border border-amber-400/40 bg-amber-500/10 p-8 space-y-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold">Strumenti amministratore</h2>
              <p class="text-sm text-amber-100/80 mt-1">Gestisci l'intero ecosistema RaceVerse: auto, hotlap, file assetto e ruoli utente.</p>
            </div>
            <a href="/admin/index.php" class="px-5 py-3 rounded-2xl bg-amber-300 text-black font-semibold text-sm">Apri pannello admin</a>
          </div>
          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 text-sm text-amber-100/80">
            <div class="p-4 rounded-2xl border border-amber-300/40 bg-black/30">Nuove auto & categorie</div>
            <div class="p-4 rounded-2xl border border-amber-300/40 bg-black/30">Aggiorna hotlap & classifiche</div>
            <div class="p-4 rounded-2xl border border-amber-300/40 bg-black/30">Carica assetti premium</div>
            <div class="p-4 rounded-2xl border border-amber-300/40 bg-black/30">Crea profili e assegna ruoli</div>
          </div>
        </section>
      <?php endif; ?>
    </div>

    <div class="mt-6 space-y-6 hidden" data-tab-panel="subscription">
      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-white/15 bg-black/40 p-6">
          <div class="text-xs uppercase tracking-[0.25em] text-white/60">Piano gratuito</div>
          <h2 class="text-2xl font-bold mt-3">RaceVerse Guest</h2>
          <p class="text-sm text-white/70 mt-2">Perfetto per iniziare subito: consulti gli hotlap pubblici, esplori la meta aggiornata e scopri quale vettura domina sulla pista scelta senza costi.</p>
          <ul class="mt-4 space-y-2 text-sm text-white/60">
            <li>• Accesso illimitato al database hotlap</li>
            <li>• Consigli su auto & combinazioni pista</li>
            <li>• Roadmap e aggiornamenti community</li>
          </ul>
          <div class="mt-6 px-4 py-3 rounded-2xl border border-white/15 bg-white/5 text-sm text-white/70">Incluso nel tuo profilo attuale.</div>
        </div>
        <div class="rounded-3xl border border-emerald-400/50 bg-emerald-500/15 p-6 relative overflow-hidden">
          <span class="absolute top-4 right-4 px-3 py-1 rounded-full text-xs uppercase tracking-[0.25em] bg-white text-black">Pro</span>
          <div class="text-xs uppercase tracking-[0.25em] text-emerald-100">Piano premium</div>
          <h2 class="text-2xl font-bold mt-3">RaceVerse Pro</h2>
          <p class="text-sm text-emerald-50/90 mt-2">La soluzione completa per replicare i tempi dei pro: scarichi tutti gli assetti ottimizzati per LMU, iRacing e ACC e ricevi aggiornamenti costanti.</p>
          <div class="mt-5 text-4xl font-black text-emerald-100">3,99€<span class="text-base font-semibold">/mese</span></div>
          <p class="text-xs text-emerald-100/80">Oppure acquista i singoli assetti a 1,99€.</p>
          <ul class="mt-5 space-y-2 text-sm text-emerald-50/80">
            <li>• Download illimitato di tutti i setup</li>
            <li>• Accesso a telemetria & consigli personalizzati</li>
            <li>• Aggiornamenti meta prioritari e Discord riservato</li>
          </ul>
          <?php if ($hasSetupAccess): ?>
            <div class="mt-6 px-4 py-3 rounded-2xl border border-emerald-200/60 bg-emerald-400/20 text-sm text-emerald-900 font-semibold">Hai già un abbonamento attivo RaceVerse Pro.</div>
          <?php else: ?>
            <a href="#" class="mt-6 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-black text-sm font-semibold">Attiva RaceVerse Pro</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="rounded-3xl border border-white/10 bg-black/30 p-6">
        <h3 class="text-lg font-semibold">Cosa include l'abbonamento</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-3 text-sm text-white/70">
          <div class="p-4 rounded-2xl border border-white/10 bg-white/5">Setup per ogni pista e condizione meteo</div>
          <div class="p-4 rounded-2xl border border-white/10 bg-white/5">Analisi telemetria condivise dai RaceVerse coach</div>
          <div class="p-4 rounded-2xl border border-white/10 bg-white/5">Supporto prioritario per richieste assetti</div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
  const tabButtons = document.querySelectorAll('[data-tab-button]');
  const tabPanels = document.querySelectorAll('[data-tab-panel]');
  const jumpLinks = document.querySelectorAll('[data-tab-jump]');

  function activateTab(target) {
    tabButtons.forEach(btn => {
      const isActive = btn.getAttribute('data-tab-target') === target;
      btn.classList.toggle('bg-white', isActive);
      btn.classList.toggle('text-black', isActive);
      btn.classList.toggle('shadow-lg', isActive);
      btn.classList.toggle('bg-white/10', !isActive);
      btn.classList.toggle('border', !isActive);
      btn.classList.toggle('border-white/20', !isActive);
      btn.classList.toggle('text-white/80', !isActive);
      btn.classList.toggle('hover:bg-white/15', !isActive);
    });
    tabPanels.forEach(panel => {
      panel.classList.toggle('hidden', panel.getAttribute('data-tab-panel') !== target);
    });
  }

  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => activateTab(btn.getAttribute('data-tab-target')));
  });

  jumpLinks.forEach(link => {
    link.addEventListener('click', event => {
      event.preventDefault();
      activateTab(link.getAttribute('data-tab-jump'));
      const subscriptionPanel = document.querySelector('[data-tab-panel="subscription"]');
      subscriptionPanel?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  activateTab('overview');
</script>
<?php include __DIR__ . '/../templates/footer.php'; ?>
