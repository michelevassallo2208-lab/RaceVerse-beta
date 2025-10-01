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
          <div class="p-3 rounded-2xl border border-white/15 bg-white/5">5€/mese • Accesso illimitato a tutti gli assetti</div>
          <div class="p-3 rounded-2xl border border-white/15 bg-white/5">1,99€ • Acquisto singolo assetto</div>
        </div>
        <a href="#" class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white text-black text-sm font-semibold">Diventa RaceVerse Pro</a>
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
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
