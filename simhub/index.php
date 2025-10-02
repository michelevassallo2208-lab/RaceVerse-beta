<?php
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';
Auth::start();
$currentUser = Auth::user();
$canDownloadSetup = Auth::isAdmin() || Auth::isPro();
$setupCtaUrl = $currentUser ? asset('payment.php') : asset('register.php');
$setupCtaLabel = $currentUser ? 'Passa a RaceVerse PRO per scaricare' : 'Registrati per scaricare l\'assetto';
$downloadSetupUrl = asset('download-setup.php');
$dbError = null;
$games = $categories = $tracks = [];
$pdo = null;

try {
  $pdo = Database::pdo();
  $games = $pdo->query("SELECT id,name FROM games ORDER BY name")->fetchAll();
  $categories = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
  $defaultGame = (int)($games[0]['id'] ?? 0);
  if ($defaultGame) {
    $st=$pdo->prepare("SELECT id,name FROM tracks WHERE game_id=? ORDER BY name");
    $st->execute([$defaultGame]);
    $tracks=$st->fetchAll();
  }
} catch (PDOException $e) {
  $dbError = "Connessione al database non disponibile. Verifica le credenziali o importa DB_DEPLOY.sql.";
  $defaultGame = 0;
}

include __DIR__ . '/templates/header.php';
?>

<section class="rounded-3xl p-8 md:p-14 bg-gradient-to-br from-indigo-600/30 via-fuchsia-500/20 to-emerald-400/20 border border-white/10 shadow-2xl shadow-indigo-500/20 overflow-hidden relative">
  <div class="absolute -top-20 -right-24 w-72 h-72 bg-emerald-400/30 blur-3xl rounded-full"></div>
  <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-indigo-500/30 blur-3xl rounded-full"></div>
  <div class="relative max-w-3xl space-y-4">
    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-black/30 border border-white/20 text-sm uppercase tracking-[0.3em]">RaceVerse Insights</span>
    <h1 class="text-4xl md:text-5xl font-black leading-tight">Trova la combinazione giusta, abbassa subito i tuoi tempi.</h1>
    <p class="text-white/80 text-lg">RaceVerse raccoglie tempi ufficiali dal tuo database, li ordina e ti mostra i riferimenti utili per preparare la prossima gara.</p>
    <div class="flex flex-col sm:flex-row gap-3">
      <a href="<?= asset('register.php') ?>" class="px-6 py-3 rounded-2xl bg-white text-black font-semibold shadow-lg shadow-white/40 hover:shadow-white/60 transition">Crea account gratuito</a>
      <a href="#pro" class="px-6 py-3 rounded-2xl bg-black/40 border border-white/20 text-white/90 hover:text-white">Scopri RaceVerse PRO</a>
    </div>
  </div>
</section>

<section id="selector" class="rounded-3xl p-6 md:p-8 bg-white/5 border border-white/10 shadow-xl shadow-indigo-500/10">
  <form class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" onsubmit="return false;">
    <div>
      <label class="block mb-1 text-sm text-white/70">Gioco</label>
      <select id="sel-game" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" <?= $games ? '' : 'disabled' ?> >
        <?php foreach($games as $g): ?>
          <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block mb-1 text-sm text-white/70">Categoria</label>
      <select id="sel-category" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" <?= $categories ? '' : 'disabled' ?>>
        <?php foreach($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block mb-1 text-sm text-white/70">Pista</label>
      <select id="sel-track" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" <?= $tracks ? '' : 'disabled' ?>>
        <?php foreach($tracks as $t): ?>
          <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-end">
      <button id="btn-search" class="w-full py-3 rounded-xl bg-white text-black font-semibold <?= $tracks ? '' : 'opacity-60 cursor-not-allowed' ?>" <?= $tracks ? '' : 'disabled' ?>>Mostra risultati</button>
    </div>
  </form>

  <div id="results" class="space-y-3">
    <?php if ($dbError): ?>
      <div class="p-4 rounded-xl bg-red-500/15 border border-red-500/30 text-red-100 text-sm"><?= htmlspecialchars($dbError) ?></div>
    <?php else: ?>
      <div class="p-4 rounded-xl bg-black/30 border border-white/10 text-gray-300">Scegli e premi “Mostra risultati”.</div>
    <?php endif; ?>
  </div>
</section>

<section id="insights" class="grid md:grid-cols-3 gap-5">
  <div class="md:col-span-2 p-6 rounded-3xl bg-black/30 border border-white/10 shadow-lg shadow-emerald-500/10">
    <h2 class="text-2xl font-bold mb-4">Statistiche esclusive</h2>
    <div class="grid md:grid-cols-2 gap-4 text-sm text-white/70">
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
        <div class="text-3xl font-black text-white">205</div>
        <div>Hotlap in archivio per LMU con aggiornamenti continui.</div>
      </div>
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
        <div class="text-3xl font-black text-white">18</div>
        <div>Vetture curate con note di setup specifiche per classe.</div>
      </div>
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
        <div class="text-3xl font-black text-white">12</div>
        <div>Piste ufficiali LMU con traiettorie e consigli in evidenza.</div>
      </div>
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
        <div class="text-3xl font-black text-white">+0.7s</div>
        <div>Vantaggio medio ottenuto dagli utenti RaceVerse PRO.</div>
      </div>
    </div>
  </div>
  <div class="p-6 rounded-3xl bg-gradient-to-br from-indigo-500/30 via-purple-500/20 to-emerald-500/30 border border-white/10 shadow-xl shadow-purple-500/20 space-y-3">
    <h3 class="text-xl font-semibold">Perché RaceVerse</h3>
    <ul class="space-y-3 text-sm text-white/80">
      <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span> Classifiche aggiornate direttamente dal tuo database.</li>
      <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-indigo-400"></span> Visual design curato per briefing squadra e streaming.</li>
      <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-fuchsia-400"></span> Filtri rapidi per incrociare categoria, pista e riferimenti piloti.</li>
    </ul>
  </div>
</section>

<section id="pro" class="rounded-3xl p-8 md:p-10 bg-black/40 border border-emerald-400/40 shadow-xl shadow-emerald-500/20">
  <div class="grid md:grid-cols-2 gap-8 items-center">
    <div class="space-y-4">
      <h2 class="text-3xl font-black text-emerald-200">RaceVerse PRO</h2>
      <p class="text-white/70 text-lg">Iscriviti gratis a RaceVerse BASIC e attiva un pass PRO quando vuoi: report avanzati, setup esclusivi e briefing pronti per il tuo team.</p>
      <ul class="space-y-2 text-white/80 text-sm">
        <li>• Libreria di setup condivisi dal team RaceVerse</li>
        <li>• Analisi tempi con confronto storico</li>
        <li>• Supporto email prioritario per richieste mirate</li>
      </ul>
      <div class="flex gap-3">
        <a href="<?= asset('login.php') ?>" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/40">Accedi</a>
        <a href="<?= asset('payment.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Acquista pass PRO</a>
      </div>
    </div>
    <div class="relative">
      <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-emerald-500/40 via-teal-500/30 to-indigo-500/30 blur-3xl"></div>
      <div class="relative rounded-3xl border border-white/10 bg-white/5 p-6 space-y-4 shadow-2xl shadow-emerald-500/20">
        <div class="text-sm uppercase tracking-[0.4em] text-white/60">Pass 30 giorni</div>
        <div class="text-4xl font-black">€2,99<span class="text-lg font-semibold text-white/70"> una tantum</span></div>
        <p class="text-white/70 text-sm">Nessun rinnovo automatico: rinnova il pass quando ti serve e mantieni la tua squadra allineata sui tempi.</p>
        <a href="<?= asset('payment.php') ?>" class="block text-center w-full py-3 rounded-2xl bg-white text-black font-semibold">Vai alla pagina Accesso PRO</a>
      </div>
    </div>
  </div>
</section>

<script>
const gameEl = document.getElementById('sel-game');
const catEl  = document.getElementById('sel-category');
const trackEl= document.getElementById('sel-track');
const results= document.getElementById('results');
const btn    = document.getElementById('btn-search');
const hasData = !!(gameEl && gameEl.options.length);
const endpoints = {
  hotlaps: <?= json_encode(asset('api/hotlaps.php')) ?>,
  tracks: <?= json_encode(asset('api/tracks.php')) ?>
};
const setupAccess = {
  canDownload: <?= json_encode($canDownloadSetup) ?>,
  downloadUrl: <?= json_encode($downloadSetupUrl) ?>,
  ctaUrl: <?= json_encode($setupCtaUrl) ?>,
  ctaLabel: <?= json_encode($setupCtaLabel) ?>
};

function fmt(ms){
  const m = Math.floor(ms/60000);
  const s = Math.floor((ms%60000)/1000);
  const mm = (ms%1000);
  return m+":"+String(s).padStart(2,'0')+"."+String(mm).padStart(3,'0');
}
function esc(value){
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
function buildAction(item){
  if (!item.setup_id){
    return `<span class="text-xs text-white/40">Assetto non disponibile</span>`;
  }
  if (setupAccess.canDownload){
    const params = new URLSearchParams({ car: item.car_id, track: item.track_id });
    return `<a href="${setupAccess.downloadUrl}?${params.toString()}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow shadow-emerald-500/30 text-sm">Download Assetto</a>`;
  }
  return `<a href="${setupAccess.ctaUrl}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white/80 hover:text-white transition text-sm">${setupAccess.ctaLabel}</a>`;
}
function card(item, idx){
  const setupBadge = item.setup_id
    ? '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-400/20 border border-emerald-300/40 text-emerald-200 text-[11px] uppercase tracking-wide">Assetto RaceVerse PRO</span>'
    : '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white/60 text-[11px] uppercase tracking-wide">Setup in lavorazione</span>';
  return `
    <div class="p-4 rounded-xl bg-black/40 border border-white/10 flex flex-col md:flex-row md:items-center gap-4">
      <div class="w-20 h-12 bg-white/5 border border-white/10 rounded overflow-hidden flex items-center justify-center">
        ${item.car_image ? `<img src="${item.car_image}" class="w-full h-full object-cover">` : `<span class="text-xs text-white/50">no img</span>`}
      </div>
      <div class="flex-1">
        <div class="text-xs text-white/60">#${idx+1}</div>
        <div class="text-lg font-semibold">${esc(item.car_name)}</div>
        <div class="text-sm text-white/80">Best: <strong>${fmt(item.lap_time_ms)}</strong> • Driver: ${esc(item.driver || '—')} • ${item.recorded_at?.slice(0,10) || ''}</div>
        ${item.notes ? `<div class="mt-2 text-xs text-white/60 leading-relaxed">${esc(item.notes)}</div>` : ''}
      </div>
      <div class="md:text-right flex md:flex-col md:items-end gap-2">
        ${setupBadge}
        ${buildAction(item)}
      </div>
    </div>`;
}
async function search(){
  if (!gameEl.value || !catEl.value || !trackEl.value) {
    results.innerHTML = `<div class="p-4 rounded-xl bg-black/30 border border-white/10 text-gray-300">Configura correttamente i filtri prima di cercare.</div>`;
    return;
  }
  results.innerHTML = `<div class="p-4 rounded-xl bg-black/30 border border-white/10">Caricamento…</div>`;
  try {
    const url = `${endpoints.hotlaps}?game=${encodeURIComponent(gameEl.value)}&category=${encodeURIComponent(catEl.value)}&track=${encodeURIComponent(trackEl.value)}`;
    const res = await fetch(url);
    if (!res.ok){ throw new Error('Risposta non valida'); }
    const data = await res.json();
    if (!data.length){
      results.innerHTML = `<div class="p-4 rounded-xl bg-black/30 border border-white/10 text-gray-300">Nessun hotlap per la combinazione selezionata.</div>`;
      return;
    }
    results.innerHTML = data.map((x,i)=>card(x,i)).join('');
  } catch (err) {
    console.error(err);
    results.innerHTML = `<div class="p-4 rounded-xl bg-red-600/20 border border-red-500/30 text-red-100">Errore nel caricamento dei dati. Controlla la connessione o riprova.</div>`;
  }
}

btn.addEventListener('click', search);

// Cambia la lista piste quando cambia il gioco
gameEl.addEventListener('change', async ()=>{
  if (!gameEl.value) { trackEl.innerHTML = ''; return; }
  trackEl.innerHTML = '<option>Caricamento…</option>';
  try {
    const res = await fetch(`${endpoints.tracks}?game=${encodeURIComponent(gameEl.value)}`);
    if (!res.ok) { throw new Error('Risposta non valida'); }
    const list = await res.json();
    trackEl.innerHTML = list.map(t=>`<option value="${t.id}">${t.name}</option>`).join('');
  } catch (err) {
    console.error(err);
    trackEl.innerHTML = '';
    results.innerHTML = `<div class="p-4 rounded-xl bg-red-600/20 border border-red-500/30 text-red-100">Errore nel caricamento piste. Controlla la configurazione del database.</div>`;
  }
});

if (!hasData) {
  gameEl?.setAttribute('disabled','disabled');
  catEl?.setAttribute('disabled','disabled');
  trackEl?.setAttribute('disabled','disabled');
}

</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
