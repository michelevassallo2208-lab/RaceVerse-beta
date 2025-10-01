<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/helpers.php';

Auth::start();
if (!Auth::isAdmin()) {
    redirect_to('login.php');
}

function admin_parse_lap_time(string $value): ?int
{
    $value = trim(str_replace(',', '.', $value));
    if ($value === '') {
        return null;
    }

    if (preg_match('/^(?:(\d+):)?([0-5]?\d)(?:\.(\d{1,3}))?$/', $value, $matches)) {
        $minutes = (int)($matches[1] ?? 0);
        $seconds = (int)$matches[2];
        if ($seconds >= 60) {
            return null;
        }
        $milliseconds = isset($matches[3]) ? (int)str_pad($matches[3], 3, '0') : 0;
        return ($minutes * 60 * 1000) + ($seconds * 1000) + $milliseconds;
    }

    return null;
}

function admin_format_lap_time(?int $milliseconds): string
{
    if (!$milliseconds) {
        return '';
    }
    $minutes = intdiv($milliseconds, 60000);
    $seconds = intdiv($milliseconds % 60000, 1000);
    $millis = $milliseconds % 1000;
    return sprintf('%d:%02d.%03d', $minutes, $seconds, $millis);
}

function admin_slugify(string $value): string
{
    $original = $value;
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);
    if ($converted !== false) {
        $value = $converted;
    }
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
    $value = trim((string)$value, '-');
    return $value !== '' ? $value : 'raceverse-' . substr(md5($original), 0, 6);
}

function admin_default_slug(int $trackId, int $carId): string
{
    return 'rv' . str_pad((string)$trackId, 2, '0', STR_PAD_LEFT) . str_pad((string)$carId, 2, '0', STR_PAD_LEFT);
}

function admin_to_datetime_value(?string $value): string
{
    if (!$value) {
        return '';
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('Y-m-d\TH:i', $timestamp) : '';
}

function admin_from_datetime_input(?string $value): ?string
{
    if (!$value) {
        return null;
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('Y-m-d H:i:00', $timestamp) : null;
}

$messages = ['success' => [], 'error' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirectParams = [];
    $messages = ['success' => [], 'error' => []];

    try {
        $pdo = Database::pdo();
    } catch (PDOException $e) {
        $messages['error'][] = 'Connessione al database non disponibile: ' . $e->getMessage();
        $_SESSION['flash'] = $messages;
        redirect_to('admin/index.php');
    }

    if ($action === 'save_hotlap') {
        $gameId = (int)($_POST['game'] ?? 0);
        $categoryId = (int)($_POST['category'] ?? 0);
        $trackId = (int)($_POST['track'] ?? 0);
        $carId = (int)($_POST['car'] ?? 0);
        $driver = trim($_POST['driver'] ?? '');
        $lapInput = trim($_POST['lap_time'] ?? '');

        $redirectParams = ['game' => $gameId, 'category' => $categoryId, 'track' => $trackId];

        if (!$gameId || !$categoryId || !$trackId || !$carId) {
            $messages['error'][] = 'Seleziona gioco, categoria, pista e vettura prima di salvare.';
        } else {
            $lapMilliseconds = admin_parse_lap_time($lapInput);
            if ($lapMilliseconds === null) {
                $messages['error'][] = 'Formato tempo non valido. Usa mm:ss.mmm (es. 1:35.250).';
            } else {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('SELECT id FROM hotlaps WHERE game_id = ? AND category_id = ? AND track_id = ? AND car_id = ? ORDER BY lap_time_ms ASC LIMIT 1');
                    $stmt->execute([$gameId, $categoryId, $trackId, $carId]);
                    $existing = $stmt->fetch();

                    if ($existing) {
                        $update = $pdo->prepare('UPDATE hotlaps SET lap_time_ms = ?, driver = ?, recorded_at = NOW() WHERE id = ?');
                        $update->execute([$lapMilliseconds, $driver !== '' ? $driver : null, $existing['id']]);
                    } else {
                        $insert = $pdo->prepare('INSERT INTO hotlaps (game_id, category_id, track_id, car_id, driver, lap_time_ms, recorded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                        $insert->execute([$gameId, $categoryId, $trackId, $carId, $driver !== '' ? $driver : null, $lapMilliseconds]);
                    }
                    $pdo->commit();
                    $messages['success'][] = 'Tempo aggiornato con successo.';
                } catch (Throwable $t) {
                    $pdo->rollBack();
                    $messages['error'][] = 'Errore durante il salvataggio del tempo: ' . $t->getMessage();
                }
            }
        }
    } elseif ($action === 'save_setup') {
        $carId = (int)($_POST['car'] ?? 0);
        $trackId = (int)($_POST['track'] ?? 0);
        $gameId = (int)($_POST['game'] ?? 0);
        $categoryId = (int)($_POST['category'] ?? 0);
        $redirectParams = ['game' => $gameId, 'category' => $categoryId, 'track' => $trackId];

        $notes = trim($_POST['notes'] ?? '');
        $fileSlug = trim($_POST['file_slug'] ?? '');
        $fileSlug = $fileSlug !== '' ? preg_replace('/\s+/', '-', $fileSlug) : admin_default_slug($trackId, $carId);

        if (!$gameId || !$categoryId || !$trackId || !$carId) {
            $messages['error'][] = 'Seleziona gioco, categoria, pista e vettura prima di salvare.';
        } else {
            $storageName = null;
            $upload = $_FILES['setup_file'] ?? null;

            if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($upload['error'] !== UPLOAD_ERR_OK) {
                    $messages['error'][] = 'Errore durante il caricamento del file (codice ' . $upload['error'] . ').';
                } elseif ($upload['size'] > 20 * 1024 * 1024) {
                    $messages['error'][] = 'Il file supera i 20 MB consentiti.';
                } else {
                    $allowedExtensions = ['zip', 'rar', '7z', 'txt', 'ini', 'json'];
                    $originalName = $upload['name'] ?? 'setup';
                    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    if ($extension && !in_array($extension, $allowedExtensions, true)) {
                        $messages['error'][] = 'Formato file non supportato. Carica zip, rar, 7z, txt, ini o json.';
                    } else {
                        $baseDir = __DIR__ . '/../assets/files';
                        if (!is_dir($baseDir)) {
                            mkdir($baseDir, 0775, true);
                        }
                        $safeBase = admin_slugify(pathinfo($originalName, PATHINFO_FILENAME));
                        try {
                            $uniqueSuffix = bin2hex(random_bytes(3));
                        } catch (Exception $e) {
                            $uniqueSuffix = substr(md5(uniqid((string)mt_rand(), true)), 0, 6);
                        }
                        $storageName = 'rv-' . $carId . '-' . $trackId . '-' . $uniqueSuffix;
                        if ($safeBase !== '') {
                            $storageName .= '-' . $safeBase;
                        }
                        if ($extension) {
                            $storageName .= '.' . $extension;
                        }
                        $destination = $baseDir . '/' . $storageName;
                        if (!move_uploaded_file($upload['tmp_name'], $destination)) {
                            $messages['error'][] = 'Impossibile salvare il file caricato.';
                            $storageName = null;
                        }
                    }
                }
            }

            if (empty($messages['error'])) {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare('SELECT id, file_path FROM car_setups WHERE car_id = ? AND track_id = ? LIMIT 1');
                    $stmt->execute([$carId, $trackId]);
                    $existing = $stmt->fetch();

                    $notesValue = $notes !== '' ? $notes : null;

                    if ($existing) {
                        $sql = 'UPDATE car_setups SET file_slug = ?, notes = ?, created_at = NOW()';
                        $params = [$fileSlug, $notesValue];
                        if ($storageName !== null) {
                            $sql .= ', file_path = ?';
                            $params[] = $storageName;
                        }
                        $sql .= ' WHERE id = ?';
                        $params[] = $existing['id'];
                        $update = $pdo->prepare($sql);
                        $update->execute($params);

                        if ($storageName !== null && $existing['file_path']) {
                            $oldPath = __DIR__ . '/../assets/files/' . $existing['file_path'];
                            if (is_file($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                    } else {
                        $insert = $pdo->prepare('INSERT INTO car_setups (car_id, track_id, file_slug, notes, file_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                        $insert->execute([$carId, $trackId, $fileSlug, $notesValue, $storageName]);
                    }

                    $pdo->commit();
                    $messages['success'][] = 'Assetto aggiornato correttamente.';
                } catch (Throwable $t) {
                    $pdo->rollBack();
                    $messages['error'][] = 'Errore durante il salvataggio dell\'assetto: ' . $t->getMessage();
                }
            }
        }
    } elseif ($action === 'update_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        $plan = trim($_POST['subscription_plan'] ?? '');
        $plan = $plan === '' ? null : $plan;
        $active = isset($_POST['subscription_active']) ? 1 : 0;
        $cancelAtPeriodEnd = isset($_POST['subscription_cancel_at_period_end']) ? 1 : 0;
        $paymentMethod = trim($_POST['subscription_payment_method'] ?? '');
        $startInput = $_POST['subscription_started_at'] ?? '';
        $renewInput = $_POST['subscription_renews_at'] ?? '';
        $newPassword = trim($_POST['new_password'] ?? '');

        if (!$userId) {
            $messages['error'][] = 'Utente non valido.';
        } else {
            $startDate = admin_from_datetime_input($startInput);
            $renewDate = admin_from_datetime_input($renewInput);

            if ($plan !== 'RaceVerse PRO') {
                $plan = $plan ?: 'RaceVerse BASIC';
            }

            if ($plan !== 'RaceVerse PRO' || !$active) {
                $startDate = null;
                $renewDate = null;
                $paymentMethod = '';
                $cancelAtPeriodEnd = 0;
            } else {
                if (!$startDate) {
                    $startDate = date('Y-m-d H:i:00');
                }
                if (!$renewDate) {
                    $renewDate = date('Y-m-d H:i:00', strtotime('+1 month'));
                }
                if ($paymentMethod === '') {
                    $paymentMethod = 'Assegnato manualmente';
                }
            }

            try {
                $sql = 'UPDATE users SET role = :role, subscription_plan = :plan, subscription_active = :active, subscription_started_at = :started_at, subscription_renews_at = :renews_at, subscription_payment_method = :payment_method, subscription_cancel_at_period_end = :cancel_at_period_end';
                $params = [
                    ':role' => $role,
                    ':plan' => $plan,
                    ':active' => $active,
                    ':started_at' => $startDate,
                    ':renews_at' => $renewDate,
                    ':payment_method' => $paymentMethod !== '' ? $paymentMethod : null,
                    ':cancel_at_period_end' => $cancelAtPeriodEnd,
                    ':id' => $userId,
                ];

                if ($newPassword !== '') {
                    $sql .= ', password_hash = :password_hash';
                    $params[':password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }

                $sql .= ' WHERE id = :id';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $messages['success'][] = 'Profilo utente aggiornato.';
            } catch (Throwable $t) {
                $messages['error'][] = 'Errore durante l\'aggiornamento utente: ' . $t->getMessage();
            }
        }
    }

    $_SESSION['flash'] = $messages;
    $query = $redirectParams ? ('?' . http_build_query($redirectParams)) : '';
    redirect_to('admin/index.php' . $query);
}

if (isset($_SESSION['flash'])) {
    $messages = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$dbError = null;
$pdo = null;
try {
    $pdo = Database::pdo();
} catch (PDOException $e) {
    $dbError = 'Connessione al database non disponibile: ' . $e->getMessage();
}

$games = $categories = $tracks = $carsForCategory = $carRows = $users = [];
$selectedGame = $selectedCategory = $selectedTrack = 0;
$selectedGameName = $selectedCategoryName = $selectedTrackName = '';

if ($pdo) {
    $games = $pdo->query('SELECT id, name FROM games ORDER BY name')->fetchAll();
    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

    if ($games) {
        $selectedGame = (int)($_GET['game'] ?? $games[0]['id']);
        $gameIds = array_column($games, 'id');
        if (!in_array($selectedGame, $gameIds, true)) {
            $selectedGame = (int)$games[0]['id'];
        }
    }

    if ($categories) {
        $selectedCategory = (int)($_GET['category'] ?? $categories[0]['id']);
        $categoryIds = array_column($categories, 'id');
        if (!in_array($selectedCategory, $categoryIds, true)) {
            $selectedCategory = (int)$categories[0]['id'];
        }
    }

    if ($selectedGame) {
        $stmtTracks = $pdo->prepare('SELECT id, name FROM tracks WHERE game_id = ? ORDER BY name');
        $stmtTracks->execute([$selectedGame]);
        $tracks = $stmtTracks->fetchAll();
        if ($tracks) {
            $selectedTrack = (int)($_GET['track'] ?? $tracks[0]['id']);
            $trackIds = array_column($tracks, 'id');
            if (!in_array($selectedTrack, $trackIds, true)) {
                $selectedTrack = (int)$tracks[0]['id'];
            }
        }
    }

    if ($selectedCategory) {
        $stmtCars = $pdo->prepare('SELECT id, name FROM cars WHERE category_id = ? ORDER BY name');
        $stmtCars->execute([$selectedCategory]);
        $carsForCategory = $stmtCars->fetchAll();
    }

    foreach ($games as $g) {
        if ((int)$g['id'] === $selectedGame) {
            $selectedGameName = $g['name'];
            break;
        }
    }
    foreach ($categories as $c) {
        if ((int)$c['id'] === $selectedCategory) {
            $selectedCategoryName = $c['name'];
            break;
        }
    }
    foreach ($tracks as $t) {
        if ((int)$t['id'] === $selectedTrack) {
            $selectedTrackName = $t['name'];
            break;
        }
    }

    if ($selectedGame && $selectedCategory && $selectedTrack) {
        $stmt = $pdo->prepare('SELECT c.id AS car_id, c.name AS car_name, c.image_path,
                                      h.id AS hotlap_id, h.lap_time_ms, h.driver, h.recorded_at,
                                      cs.id AS setup_id, cs.file_slug, cs.notes, cs.file_path
                               FROM cars c
                               LEFT JOIN hotlaps h ON h.car_id = c.id AND h.track_id = :track AND h.game_id = :game AND h.category_id = :category
                               LEFT JOIN car_setups cs ON cs.car_id = c.id AND cs.track_id = :track
                               WHERE c.category_id = :category
                               ORDER BY c.name');
        $stmt->execute([
            ':track' => $selectedTrack,
            ':game' => $selectedGame,
            ':category' => $selectedCategory,
        ]);
        $carRows = $stmt->fetchAll();
    }

    $users = $pdo->query('SELECT id, email, first_name, last_name, role, subscription_plan, subscription_active, subscription_started_at, subscription_renews_at, subscription_payment_method, subscription_cancel_at_period_end, created_at FROM users ORDER BY created_at DESC')->fetchAll();
}

?><!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin • RaceVerse</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= asset('../assets/css/style.css') ?>">
</head>
<body class="bg-[#05060b] text-white premium-texture min-h-screen">
  <div class="max-w-6xl mx-auto px-4 py-10 space-y-8">
    <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div class="flex items-center gap-4">
        <span class="inline-flex items-center justify-center w-14 h-14 rounded-3xl bg-gradient-to-br from-indigo-500 via-purple-500 to-emerald-500 shadow-lg shadow-indigo-500/40">
          <img src="<?= asset('../assets/images/logo.png') ?>" class="w-9 h-9" alt="RaceVerse logo">
        </span>
        <div>
          <h1 class="text-3xl font-bold">Pannello RaceVerse</h1>
          <p class="text-white/60">Gestisci tempi, assetti e utenti RaceVerse PRO in un unico ambiente.</p>
        </div>
      </div>
      <div class="flex gap-3 flex-wrap">
        <a href="<?= asset('tickets.php') ?>" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30">Ticket supporto</a>
        <a href="<?= asset('../index.php') ?>" class="px-5 py-3 rounded-2xl bg-white/10 border border-white/10 text-white/80 hover:text-white">← Torna al sito</a>
        <a href="<?= asset('../logout.php') ?>" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-rose-500 via-fuchsia-500 to-purple-500 text-black font-semibold shadow-lg shadow-rose-500/30">Logout</a>
      </div>
    </header>

    <?php foreach ($messages['success'] ?? [] as $message): ?>
      <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-200 text-sm flex items-center gap-3">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span><?= htmlspecialchars($message) ?></span>
      </div>
    <?php endforeach; ?>

    <?php foreach ($messages['error'] ?? [] as $message): ?>
      <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-400/40 text-rose-200 text-sm flex items-center gap-3">
        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
        <span><?= htmlspecialchars($message) ?></span>
      </div>
    <?php endforeach; ?>

    <?php if ($dbError): ?>
      <div class="p-6 rounded-3xl bg-rose-500/15 border border-rose-400/30 text-rose-100">
        <?= htmlspecialchars($dbError) ?>
      </div>
    <?php else: ?>
      <section class="rounded-3xl bg-black/60 border border-white/10 shadow-2xl shadow-indigo-500/20 p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <h2 class="text-2xl font-semibold">Seleziona combinazione</h2>
            <p class="text-sm text-white/60">Scegli gioco, categoria e pista per gestire tempi e assetti di ogni vettura.</p>
          </div>
        </div>
        <form id="filters" method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <label class="space-y-2 text-sm">
            <span class="text-white/60">Gioco</span>
            <select name="game" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2">
              <?php foreach ($games as $game): ?>
                <option value="<?= (int)$game['id'] ?>" <?= $selectedGame === (int)$game['id'] ? 'selected' : '' ?>><?= htmlspecialchars($game['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-white/60">Categoria</span>
            <select name="category" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2">
              <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['id'] ?>" <?= $selectedCategory === (int)$category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-white/60">Pista</span>
            <select name="track" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2">
              <?php foreach ($tracks as $track): ?>
                <option value="<?= (int)$track['id'] ?>" <?= $selectedTrack === (int)$track['id'] ? 'selected' : '' ?>><?= htmlspecialchars($track['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
        </form>
        <script>
          document.querySelectorAll('#filters select').forEach((el) => {
            el.addEventListener('change', () => {
              document.getElementById('filters').submit();
            });
          });
        </script>
      </section>

      <section class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-2xl font-semibold">Tempi & Assetti</h2>
          <p class="text-sm text-white/50"><?= htmlspecialchars(trim($selectedCategoryName . ' • ' . $selectedTrackName)) ?></p>
        </div>
        <?php if ($carRows): ?>
          <div class="grid gap-5 md:grid-cols-2">
            <?php foreach ($carRows as $row): ?>
              <div class="rounded-3xl bg-black/60 border border-white/10 p-5 space-y-4 shadow-lg shadow-indigo-500/10">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-lg font-semibold"><?= htmlspecialchars($row['car_name']) ?></h3>
                    <p class="text-xs text-white/40">ID vettura: <?= (int)$row['car_id'] ?></p>
                  </div>
                  <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs text-white/60">Hotlap</span>
                </div>
                <form method="post" class="space-y-3">
                  <input type="hidden" name="action" value="save_hotlap">
                  <input type="hidden" name="game" value="<?= $selectedGame ?>">
                  <input type="hidden" name="category" value="<?= $selectedCategory ?>">
                  <input type="hidden" name="track" value="<?= $selectedTrack ?>">
                  <input type="hidden" name="car" value="<?= (int)$row['car_id'] ?>">
                  <label class="block text-sm space-y-1">
                    <span class="text-white/60">Tempo (mm:ss.mmm)</span>
                    <input type="text" name="lap_time" value="<?= htmlspecialchars(admin_format_lap_time($row['lap_time_ms'])) ?>" placeholder="es. 1:35.250" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                  </label>
                  <label class="block text-sm space-y-1">
                    <span class="text-white/60">Pilota</span>
                    <input type="text" name="driver" value="<?= htmlspecialchars($row['driver'] ?? '') ?>" placeholder="RaceVerse Team" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                  </label>
                  <button type="submit" class="w-full py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Salva tempo</button>
                  <?php if (!empty($row['lap_time_ms'])): ?>
                    <p class="text-[11px] text-white/40">Ultimo aggiornamento: <?= htmlspecialchars($row['recorded_at'] ?? '—') ?></p>
                  <?php endif; ?>
                </form>

                <div class="border-t border-white/10 pt-4 space-y-3">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-white/70">Assetto RaceVerse PRO</span>
                    <?php if (!empty($row['file_path'])): ?>
                      <a href="<?= htmlspecialchars(asset('../download-setup.php?car=' . (int)$row['car_id'] . '&track=' . $selectedTrack)) ?>" class="text-emerald-300 text-xs hover:text-emerald-200">Scarica attuale</a>
                    <?php else: ?>
                      <span class="text-white/30 text-xs">Non assegnato</span>
                    <?php endif; ?>
                  </div>
                  <form method="post" enctype="multipart/form-data" class="space-y-3">
                    <input type="hidden" name="action" value="save_setup">
                    <input type="hidden" name="game" value="<?= $selectedGame ?>">
                    <input type="hidden" name="category" value="<?= $selectedCategory ?>">
                    <input type="hidden" name="track" value="<?= $selectedTrack ?>">
                    <input type="hidden" name="car" value="<?= (int)$row['car_id'] ?>">
                    <label class="block text-sm space-y-1">
                      <span class="text-white/60">Identificativo file</span>
                      <input type="text" name="file_slug" value="<?= htmlspecialchars($row['file_slug'] ?? '') ?>" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </label>
                    <label class="block text-sm space-y-1">
                      <span class="text-white/60">Note</span>
                      <textarea name="notes" rows="3" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Note sull'assetto"><?= htmlspecialchars($row['notes'] ?? '') ?></textarea>
                    </label>
                    <label class="block text-sm space-y-1">
                      <span class="text-white/60">Carica file assetto</span>
                      <input type="file" name="setup_file" class="w-full text-xs text-white/70">
                      <span class="text-[11px] text-white/30">Formati supportati: zip, rar, 7z, txt, ini, json (max 20 MB)</span>
                    </label>
                    <button type="submit" class="w-full py-2 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30">Salva assetto</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="p-6 rounded-3xl bg-black/50 border border-white/10 text-white/60">
            Nessuna vettura trovata per la combinazione selezionata.
          </div>
        <?php endif; ?>
      </section>

      <section class="rounded-3xl bg-black/60 border border-white/10 shadow-2xl shadow-purple-500/20 p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h2 class="text-2xl font-semibold">Gestione utenti</h2>
            <p class="text-sm text-white/60">Aggiorna ruoli, abbonamenti e password direttamente dal pannello.</p>
          </div>
        </div>
        <?php if ($users): ?>
          <div class="grid gap-5 md:grid-cols-2">
            <?php foreach ($users as $user): ?>
              <div class="rounded-3xl bg-black/50 border border-white/10 p-5 space-y-3">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-lg font-semibold"><?= htmlspecialchars($user['email']) ?></h3>
                    <p class="text-xs text-white/40">Creato il <?= htmlspecialchars(date('d/m/Y H:i', strtotime($user['created_at']))) ?></p>
                  </div>
                  <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs text-white/60"><?= $user['role'] === 'admin' ? 'Admin' : 'User' ?></span>
                </div>
                <form method="post" class="space-y-3">
                  <input type="hidden" name="action" value="update_user">
                  <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <label class="space-y-1">
                      <span class="text-white/60">Ruolo</span>
                      <select name="role" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                      </select>
                    </label>
                    <label class="space-y-1">
                      <span class="text-white/60">Piano</span>
                      <select name="subscription_plan" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                        <option value="RaceVerse BASIC" <?= $user['subscription_plan'] === 'RaceVerse BASIC' || !$user['subscription_plan'] ? 'selected' : '' ?>>RaceVerse BASIC</option>
                        <option value="RaceVerse PRO" <?= $user['subscription_plan'] === 'RaceVerse PRO' ? 'selected' : '' ?>>RaceVerse PRO</option>
                      </select>
                    </label>
                  </div>
                  <label class="flex items-center gap-2 text-sm text-white/70">
                    <input type="checkbox" name="subscription_active" value="1" <?= $user['subscription_active'] ? 'checked' : '' ?> class="rounded border-white/20 bg-white/5">
                    Abbonamento attivo
                  </label>
                  <label class="flex items-center gap-2 text-sm text-white/70">
                    <input type="checkbox" name="subscription_cancel_at_period_end" value="1" <?= $user['subscription_cancel_at_period_end'] ? 'checked' : '' ?> class="rounded border-white/20 bg-white/5">
                    Cancellazione a fine periodo
                  </label>
                  <label class="block text-sm space-y-1">
                    <span class="text-white/60">Metodo di pagamento</span>
                    <input type="text" name="subscription_payment_method" value="<?= htmlspecialchars($user['subscription_payment_method'] ?? '') ?>" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                  </label>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <label class="space-y-1">
                      <span class="text-white/60">Attivo dal</span>
                      <input type="datetime-local" name="subscription_started_at" value="<?= htmlspecialchars(admin_to_datetime_value($user['subscription_started_at'])) ?>" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                    </label>
                    <label class="space-y-1">
                      <span class="text-white/60">Rinnovo</span>
                      <input type="datetime-local" name="subscription_renews_at" value="<?= htmlspecialchars(admin_to_datetime_value($user['subscription_renews_at'])) ?>" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                    </label>
                  </div>
                  <label class="block text-sm space-y-1">
                    <span class="text-white/60">Nuova password</span>
                    <input type="password" name="new_password" placeholder="Lascia vuoto per non cambiare" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                  </label>
                  <button type="submit" class="w-full py-2 rounded-xl bg-gradient-to-r from-purple-500 via-indigo-500 to-blue-500 text-black font-semibold shadow-lg shadow-purple-500/30">Aggiorna utente</button>
                  <p class="text-[11px] text-white/30">Piano: <?= htmlspecialchars($user['subscription_plan'] ?? 'RaceVerse BASIC') ?> • Stato: <?= $user['subscription_active'] ? 'attivo' : 'non attivo' ?></p>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="p-6 rounded-3xl bg-black/50 border border-white/10 text-white/60">Nessun utente trovato.</div>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </div>
</body>
</html>
