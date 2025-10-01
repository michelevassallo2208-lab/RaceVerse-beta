<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
if (!Auth::isAdmin()) {
    header('Location: /login.php');
    exit;
}
$pdo = Database::pdo();
$feedback = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_hotlap') {
        $gameId = (int)($_POST['game_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $trackId = (int)($_POST['track_id'] ?? 0);
        $carId = (int)($_POST['car_id'] ?? 0);
        $driver = trim($_POST['driver'] ?? '');
        $lapTime = trim($_POST['lap_time'] ?? '');
        if ($gameId && $categoryId && $trackId && $carId && $lapTime !== '') {
            if (preg_match('/^(\d+):(\d{2})\.(\d{1,3})$/', $lapTime, $m)) {
                $minutes = (int)$m[1];
                $seconds = (int)$m[2];
                $millis = (int)str_pad($m[3], 3, '0');
                $lapMs = ($minutes * 60 * 1000) + ($seconds * 1000) + $millis;
                $st = $pdo->prepare('INSERT INTO hotlaps (game_id, category_id, track_id, car_id, driver, lap_time_ms) VALUES (?,?,?,?,?,?)');
                $st->execute([$gameId, $categoryId, $trackId, $carId, $driver ?: null, $lapMs]);
                $feedback = 'Hotlap aggiunto correttamente.';
            } else {
                $error = 'Formato tempo non valido. Usa mm:ss.mmm (es. 1:32.450).';
            }
        } else {
            $error = 'Compila tutti i campi richiesti.';
        }
    } elseif ($action === 'delete_hotlap') {
        $id = (int)($_POST['hotlap_id'] ?? 0);
        if ($id) {
            $st = $pdo->prepare('DELETE FROM hotlaps WHERE id = ?');
            $st->execute([$id]);
            $feedback = 'Hotlap eliminato.';
        }
    } elseif ($action === 'upload_setup') {
        $gameId = (int)($_POST['game_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $trackId = (int)($_POST['track_id'] ?? 0);
        $carId = (int)($_POST['car_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if ($gameId && $categoryId && $trackId && $carId && $title && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/setups';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $tmpName = $_FILES['file']['tmp_name'];
            $origName = basename($_FILES['file']['name']);
            $safeName = uniqid('setup_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
            $destPath = $uploadDir . '/' . $safeName;
            if (move_uploaded_file($tmpName, $destPath)) {
                $publicPath = '/uploads/setups/' . $safeName;
                $st = $pdo->prepare('INSERT INTO setups (game_id, category_id, track_id, car_id, title, file_path) VALUES (?,?,?,?,?,?)');
                $st->execute([$gameId, $categoryId, $trackId, $carId, $title, $publicPath]);
                $feedback = 'Setup caricato con successo.';
            } else {
                $error = 'Impossibile spostare il file caricato.';
            }
        } else {
            $error = 'Compila tutti i campi e seleziona un file valido.';
        }
    } elseif ($action === 'delete_setup') {
        $id = (int)($_POST['setup_id'] ?? 0);
        if ($id) {
            $st = $pdo->prepare('SELECT file_path FROM setups WHERE id = ?');
            $st->execute([$id]);
            $setup = $st->fetch();
            if ($setup) {
                $filePath = __DIR__ . '/../public' . $setup['file_path'];
                if (is_file($filePath)) {
                    unlink($filePath);
                }
                $pdo->prepare('DELETE FROM setups WHERE id = ?')->execute([$id]);
                $feedback = 'Setup eliminato.';
            }
        }
    }
}
$games = $pdo->query('SELECT id,name FROM games ORDER BY name')->fetchAll();
$categories = $pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$tracks = $pdo->query('SELECT id,name FROM tracks ORDER BY name')->fetchAll();
$cars = $pdo->query('SELECT id,name FROM cars ORDER BY name')->fetchAll();
$hotlaps = $pdo->query('SELECT h.id, h.driver, h.lap_time_ms, h.recorded_at, t.name AS track_name, c.name AS car_name
    FROM hotlaps h
    JOIN tracks t ON t.id = h.track_id
    JOIN cars c ON c.id = h.car_id
    ORDER BY h.recorded_at DESC LIMIT 20')->fetchAll();
$setups = $pdo->query('SELECT s.id, s.title, s.file_path, s.created_at, t.name AS track_name, c.name AS car_name
    FROM setups s
    JOIN tracks t ON t.id = s.track_id
    JOIN cars c ON c.id = s.car_id
    ORDER BY s.created_at DESC LIMIT 20')->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hotlap & Setup • Raceverse</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-6xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold">Gestione hotlap & setup</h1>
      <a href="/dashboard.php" class="text-sm underline">← Torna alla dashboard</a>
    </div>
    <?php if ($feedback): ?>
      <div class="mb-4 p-3 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 text-sm"><?= htmlspecialchars($feedback) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700 border border-red-300 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <form method="post" class="bg-white rounded-2xl shadow p-6 space-y-4">
        <input type="hidden" name="action" value="add_hotlap">
        <h2 class="text-xl font-semibold">Aggiungi hotlap</h2>
        <div class="grid grid-cols-1 gap-3">
          <label class="text-sm">Gioco
            <select name="game_id" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="">Seleziona</option>
              <?php foreach ($games as $game): ?>
                <option value="<?= (int)$game['id'] ?>"><?= htmlspecialchars($game['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm">Categoria
            <select name="category_id" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="">Seleziona</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm">Pista
            <select name="track_id" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="">Seleziona</option>
              <?php foreach ($tracks as $track): ?>
                <option value="<?= (int)$track['id'] ?>"><?= htmlspecialchars($track['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm">Auto
            <select name="car_id" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="">Seleziona</option>
              <?php foreach ($cars as $car): ?>
                <option value="<?= (int)$car['id'] ?>"><?= htmlspecialchars($car['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm">Driver
            <input type="text" name="driver" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="Nome pilota (facoltativo)">
          </label>
          <label class="text-sm">Tempo lap <span class="text-xs text-gray-500">(formato mm:ss.mmm)</span>
            <input type="text" name="lap_time" class="w-full border rounded-lg px-3 py-2 mt-1" required>
          </label>
        </div>
        <button class="w-full py-2 rounded-lg bg-indigo-600 text-white font-semibold">Salva hotlap</button>
      </form>
      <form method="post" enctype="multipart/form-data" class="bg-white rounded-2xl shadow p-6 space-y-4" id="upload">
        <input type="hidden" name="action" value="upload_setup">
        <h2 class="text-xl font-semibold">Carica setup</h2>
        <div class="grid grid-cols-1 gap-3">
          <label class="text-sm">Gioco
            <select name="game_id" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="">Seleziona</option>
              <?php foreach ($games as $game): ?>
                <option value="<?= (int)$game['id'] ?>"><?= htmlspecialchars($game['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm">Categoria
            <select name="category_id" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="">Seleziona</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm">Pista
            <select name="track_id" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="">Seleziona</option>
              <?php foreach ($tracks as $track): ?>
                <option value="<?= (int)$track['id'] ?>"><?= htmlspecialchars($track['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm">Auto
            <select name="car_id" class="w-full border rounded-lg px-3 py-2 mt-1">
              <option value="">Seleziona</option>
              <?php foreach ($cars as $car): ?>
                <option value="<?= (int)$car['id'] ?>"><?= htmlspecialchars($car['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm">Titolo setup
            <input type="text" name="title" class="w-full border rounded-lg px-3 py-2 mt-1" required>
          </label>
          <label class="text-sm">File setup
            <input type="file" name="file" class="w-full border rounded-lg px-3 py-2 mt-1" required>
          </label>
        </div>
        <button class="w-full py-2 rounded-lg bg-indigo-600 text-white font-semibold">Carica setup</button>
      </form>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Ultimi hotlap</h2>
        <ul class="space-y-3">
          <?php foreach ($hotlaps as $item): ?>
            <li class="p-3 rounded-xl border border-gray-200">
              <div class="text-sm text-gray-500 flex justify-between">
                <span><?= htmlspecialchars($item['track_name']) ?> • <?= htmlspecialchars($item['car_name']) ?></span>
                <span><?= htmlspecialchars(substr($item['recorded_at'], 0, 10)) ?></span>
              </div>
              <div class="text-lg font-semibold text-gray-900"><?= number_format($item['lap_time_ms'] / 1000, 3) ?> s</div>
              <div class="text-xs text-gray-500 mb-2">Driver: <?= htmlspecialchars($item['driver'] ?: '—') ?></div>
              <form method="post" class="inline">
                <input type="hidden" name="action" value="delete_hotlap">
                <input type="hidden" name="hotlap_id" value="<?= (int)$item['id'] ?>">
                <button class="text-xs text-red-600 underline" onclick="return confirm('Eliminare questo hotlap?');">Elimina</button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Ultimi setup</h2>
        <ul class="space-y-3">
          <?php foreach ($setups as $setup): ?>
            <li class="p-3 rounded-xl border border-gray-200">
              <div class="text-sm text-gray-500 flex justify-between">
                <span><?= htmlspecialchars($setup['track_name']) ?> • <?= htmlspecialchars($setup['car_name']) ?></span>
                <span><?= htmlspecialchars(substr($setup['created_at'], 0, 10)) ?></span>
              </div>
              <div class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($setup['title']) ?></div>
              <div class="flex items-center gap-3 text-xs text-gray-600">
                <a href="<?= htmlspecialchars($setup['file_path']) ?>" class="underline" download>Scarica</a>
                <form method="post" onsubmit="return confirm('Eliminare questo setup?');">
                  <input type="hidden" name="action" value="delete_setup">
                  <input type="hidden" name="setup_id" value="<?= (int)$setup['id'] ?>">
                  <button class="text-red-600 underline">Elimina</button>
                </form>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</body>
</html>
