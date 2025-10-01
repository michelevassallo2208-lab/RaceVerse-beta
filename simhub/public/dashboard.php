<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$user = Auth::user();
if (!$user) {
    header('Location: /login.php');
    exit;
}
$pdo = Database::pdo();
$games = $pdo->query("SELECT id,name FROM games ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$defaultGame = (int)($games[0]['id'] ?? 0);
$tracks = [];
if ($defaultGame) {
    $st = $pdo->prepare("SELECT id,name FROM tracks WHERE game_id=? ORDER BY name");
    $st->execute([$defaultGame]);
    $tracks = $st->fetchAll();
}
$latestHotlaps = $pdo->query("SELECT h.id, h.lap_time_ms, h.driver, h.recorded_at, t.name AS track_name, c.name AS car_name
    FROM hotlaps h
    JOIN tracks t ON t.id = h.track_id
    JOIN cars c ON c.id = h.car_id
    ORDER BY h.recorded_at DESC
    LIMIT 5")->fetchAll();
$latestSetups = $pdo->query("SELECT s.id, s.title, s.file_path, s.created_at, t.name AS track_name, c.name AS car_name
    FROM setups s
    JOIN tracks t ON t.id = s.track_id
    JOIN cars c ON c.id = s.car_id
    ORDER BY s.created_at DESC
    LIMIT 5")->fetchAll();
include __DIR__ . '/../templates/header.php';
?>
<section class="rounded-3xl p-6 md:p-8 bg-white/5 border border-white/10 mb-8">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-3xl font-bold">Benvenuto nella tua dashboard</h1>
      <p class="text-white/70">Ciao <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>, gestisci hotlap e setup da un'unica schermata.</p>
    </div>
    <div class="text-sm text-white/70">
      Ruolo: <strong><?= htmlspecialchars($user['role']) ?></strong><br>
      Piano: <strong><?= htmlspecialchars($user['subscription_plan'] ?: 'Nessuno') ?></strong>
    </div>
  </div>
</section>
<section id="hotlaps" class="rounded-3xl p-6 md:p-8 bg-white/5 border border-white/10 mb-8">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
    <div>
      <h2 class="text-2xl font-semibold">Selettore Hotlap</h2>
      <p class="text-white/60 text-sm">Filtra per gioco, categoria e pista per visualizzare i migliori tempi.</p>
    </div>
    <?php if (Auth::isAdmin()): ?>
      <a href="/admin/hotlaps.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-black text-sm font-semibold">Gestione hotlap</a>
    <?php endif; ?>
  </div>
  <form class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" onsubmit="return false;">
    <div>
      <label class="block mb-1 text-sm text-white/70">Gioco</label>
      <select id="dash-game" class="w-full p-3 rounded-xl bg-white/5 border border-white/20">
        <?php foreach ($games as $game): ?>
          <option value="<?= (int)$game['id'] ?>"><?= htmlspecialchars($game['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block mb-1 text-sm text-white/70">Categoria</label>
      <select id="dash-category" class="w-full p-3 rounded-xl bg-white/5 border border-white/20">
        <?php foreach ($categories as $category): ?>
          <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block mb-1 text-sm text-white/70">Pista</label>
      <select id="dash-track" class="w-full p-3 rounded-xl bg-white/5 border border-white/20">
        <?php foreach ($tracks as $track): ?>
          <option value="<?= (int)$track['id'] ?>"><?= htmlspecialchars($track['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-end">
      <button id="dash-search" class="w-full py-3 rounded-xl bg-white text-black font-semibold">Mostra risultati</button>
    </div>
  </form>
  <div id="dash-results" class="space-y-3">
    <div class="p-4 rounded-xl bg-black/30 border border-white/10 text-gray-300">Seleziona filtri e premi "Mostra risultati".</div>
  </div>
</section>
<section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
  <div class="rounded-3xl p-6 bg-white/5 border border-white/10">
    <h3 class="text-xl font-semibold mb-3">Ultimi hotlap</h3>
    <ul class="space-y-3">
      <?php if ($latestHotlaps): foreach ($latestHotlaps as $item): ?>
        <li class="p-3 rounded-xl bg-black/30 border border-white/10">
          <div class="text-sm text-white/60 flex justify-between">
            <span><?= htmlspecialchars($item['track_name']) ?> • <?= htmlspecialchars($item['car_name']) ?></span>
            <span><?= htmlspecialchars(substr($item['recorded_at'], 0, 10)) ?></span>
          </div>
          <div class="text-lg font-semibold text-white"><?= number_format($item['lap_time_ms'] / 1000, 3) ?> s</div>
          <div class="text-sm text-white/70">Driver: <?= htmlspecialchars($item['driver'] ?: '—') ?></div>
        </li>
      <?php endforeach; else: ?>
        <li class="p-3 rounded-xl bg-black/20 border border-white/10 text-sm text-white/60">Nessun hotlap caricato.</li>
      <?php endif; ?>
    </ul>
  </div>
  <div id="setups" class="rounded-3xl p-6 bg-white/5 border border-white/10">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-xl font-semibold">Ultimi setup caricati</h3>
      <?php if (Auth::isAdmin()): ?>
        <a href="/admin/hotlaps.php#upload" class="text-sm underline">Carica setup</a>
      <?php endif; ?>
    </div>
    <ul class="space-y-3">
      <?php if ($latestSetups): foreach ($latestSetups as $setup): ?>
        <li class="p-3 rounded-xl bg-black/30 border border-white/10">
          <div class="text-sm text-white/60 flex justify-between">
            <span><?= htmlspecialchars($setup['track_name']) ?> • <?= htmlspecialchars($setup['car_name']) ?></span>
            <span><?= htmlspecialchars(substr($setup['created_at'], 0, 10)) ?></span>
          </div>
          <div class="text-lg font-semibold text-white"><?= htmlspecialchars($setup['title']) ?></div>
          <a class="text-sm text-white/80 underline" href="<?= htmlspecialchars($setup['file_path']) ?>" download>Scarica</a>
        </li>
      <?php endforeach; else: ?>
        <li class="p-3 rounded-xl bg-black/20 border border-white/10 text-sm text-white/60">Nessun setup caricato.</li>
      <?php endif; ?>
    </ul>
  </div>
</section>
<?php if (Auth::isAdmin()): ?>
  <section class="rounded-3xl p-6 md:p-8 bg-amber-500/10 border border-amber-500/20 mb-8">
    <h2 class="text-2xl font-semibold mb-2 text-amber-100">Strumenti amministratore</h2>
    <p class="text-amber-200 text-sm mb-4">Gestisci utenti e contenuti speciali di Raceverse.</p>
    <div class="flex flex-wrap gap-3">
      <a href="/admin/accounts.php" class="px-4 py-2 rounded-xl bg-white text-black font-semibold">Gestione account</a>
      <a href="/admin/hotlaps.php" class="px-4 py-2 rounded-xl border border-white/40 text-white">Gestione hotlap & setup</a>
    </div>
  </section>
<?php endif; ?>
<script>
const dashGame = document.getElementById('dash-game');
const dashCategory = document.getElementById('dash-category');
const dashTrack = document.getElementById('dash-track');
const dashResults = document.getElementById('dash-results');
const dashSearch = document.getElementById('dash-search');
function formatLap(ms) {
  const minutes = Math.floor(ms / 60000);
  const seconds = Math.floor((ms % 60000) / 1000);
  const millis = ms % 1000;
  return minutes + ':' + String(seconds).padStart(2, '0') + '.' + String(millis).padStart(3, '0');
}
async function loadTracksForGame(gameId) {
  const res = await fetch(`/api/tracks.php?game=${encodeURIComponent(gameId)}`);
  if (!res.ok) return;
  const data = await res.json();
  dashTrack.innerHTML = data.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
}
dashGame?.addEventListener('change', () => loadTracksForGame(dashGame.value));
dashSearch?.addEventListener('click', async () => {
  dashResults.innerHTML = '<div class="p-4 rounded-xl bg-black/30 border border-white/10">Caricamento…</div>';
  const url = `/api/hotlaps.php?game=${encodeURIComponent(dashGame.value)}&category=${encodeURIComponent(dashCategory.value)}&track=${encodeURIComponent(dashTrack.value)}`;
  const res = await fetch(url);
  if (!res.ok) {
    dashResults.innerHTML = '<div class="p-4 rounded-xl bg-red-600/20 border border-red-500/30 text-red-100">Errore nel caricamento</div>';
    return;
  }
  const data = await res.json();
  if (!data.length) {
    dashResults.innerHTML = '<div class="p-4 rounded-xl bg-black/30 border border-white/10 text-gray-300">Nessun hotlap trovato.</div>';
    return;
  }
  dashResults.innerHTML = data.map((item, index) => `
    <div class="p-4 rounded-xl bg-black/40 border border-white/10 flex flex-col md:flex-row md:items-center gap-4">
      <div class="text-4xl font-bold text-white/60">#${index + 1}</div>
      <div class="flex-1">
        <div class="text-lg font-semibold text-white">${item.car_name}</div>
        <div class="text-sm text-white/70">Driver: ${item.driver || '—'} • ${item.recorded_at?.slice(0,10) || ''}</div>
      </div>
      <div class="text-xl font-semibold text-white">${formatLap(parseInt(item.lap_time_ms, 10))}</div>
    </div>
  `).join('');
});
</script>
<?php include __DIR__ . '/../templates/footer.php'; ?>
