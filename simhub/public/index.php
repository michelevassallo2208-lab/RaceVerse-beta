<?php
require_once __DIR__ . '/../src/Database.php';
$pdo = Database::pdo();
$games = $pdo->query("SELECT id,name FROM games ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$defaultGame = (int)($games[0]['id'] ?? 1);
$tracks = [];
$st=$pdo->prepare("SELECT id,name FROM tracks WHERE game_id=? ORDER BY name");
$st->execute([$defaultGame]);
$tracks=$st->fetchAll();

include __DIR__ . '/../templates/header.php';
?>

<section class="rounded-3xl p-8 md:p-12 bg-gradient-to-br from-indigo-600/20 via-pink-500/10 to-emerald-500/10 border border-white/10 shadow-xl mb-10">
  <div class="max-w-3xl">
    <h1 class="text-4xl md:text-5xl font-extrabold mb-3">Trova l'auto top per ogni pista</h1>
    <p class="text-white/80 text-lg">Hotlap dei pro, meta sempre aggiornato. Gratis per i dati. Con <strong>Raceverse Pro</strong> scarichi gli assetti premium.</p>
  </div>
</section>

<section id="selector" class="rounded-3xl p-6 md:p-8 bg-white/5 border border-white/10 mb-8">
  <form class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" onsubmit="return false;">
    <div>
      <label class="block mb-1 text-sm text-white/70">Gioco</label>
      <select id="sel-game" class="w-full p-3 rounded-xl bg-white/5 border border-white/20">
        <?php foreach($games as $g): ?>
          <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block mb-1 text-sm text-white/70">Categoria</label>
      <select id="sel-category" class="w-full p-3 rounded-xl bg-white/5 border border-white/20">
        <?php foreach($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block mb-1 text-sm text-white/70">Pista</label>
      <select id="sel-track" class="w-full p-3 rounded-xl bg-white/5 border border-white/20">
        <?php foreach($tracks as $t): ?>
          <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-end">
      <button id="btn-search" class="w-full py-3 rounded-xl bg-white text-black font-semibold">Mostra risultati</button>
    </div>
  </form>

  <div id="results" class="space-y-3">
    <div class="p-4 rounded-xl bg-black/30 border border-white/10 text-gray-300">Scegli e premi “Mostra risultati”.</div>
  </div>
</section>

<script>
const gameEl = document.getElementById('sel-game');
const catEl  = document.getElementById('sel-category');
const trackEl= document.getElementById('sel-track');
const results= document.getElementById('results');
const btn    = document.getElementById('btn-search');

function fmt(ms){
  const m = Math.floor(ms/60000);
  const s = Math.floor((ms%60000)/1000);
  const mm = (ms%1000);
  return m+":"+String(s).padStart(2,'0')+"."+String(mm).padStart(3,'0');
}
function card(item, idx){
  return `
    <div class="p-4 rounded-xl bg-black/40 border border-white/10 flex items-center gap-4">
      <div class="w-20 h-12 bg-white/5 border border-white/10 rounded overflow-hidden flex items-center justify-center">
        ${item.car_image ? `<img src="${item.car_image}" class="w-full h-full object-cover">` : `<span class="text-xs text-white/50">no img</span>`}
      </div>
      <div class="flex-1">
        <div class="text-xs text-white/60">#${idx+1}</div>
        <div class="text-lg font-semibold">${item.car_name}</div>
        <div class="text-sm text-white/80">Best: <strong>${fmt(item.lap_time_ms)}</strong> • Driver: ${item.driver || '—'} • ${item.recorded_at?.slice(0,10) || ''}</div>
      </div>
    </div>`;
}
async function search(){
  results.innerHTML = `<div class="p-4 rounded-xl bg-black/30 border border-white/10">Caricamento…</div>`;
  const url = `/api/hotlaps.php?game=${encodeURIComponent(gameEl.value)}&category=${encodeURIComponent(catEl.value)}&track=${encodeURIComponent(trackEl.value)}`;
  const res = await fetch(url);
  if (!res.ok){ results.innerHTML = `<div class="p-4 rounded-xl bg-red-600/20 border border-red-500/30 text-red-100">Errore nel caricamento</div>`; return; }
  const data = await res.json();
  if (!data.length){ results.innerHTML = `<div class="p-4 rounded-xl bg-black/30 border border-white/10 text-gray-300">Nessun hotlap per la combinazione selezionata.</div>`; return; }
  results.innerHTML = data.map((x,i)=>card(x,i)).join('');
}

btn.addEventListener('click', search);

// Cambia la lista piste quando cambia il gioco
gameEl.addEventListener('change', async ()=>{
  const res = await fetch('/api/tracks.php?game='+encodeURIComponent(gameEl.value));
  const list = await res.json();
  trackEl.innerHTML = list.map(t=>`<option value="${t.id}">${t.name}</option>`).join('');
});

</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
