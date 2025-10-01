<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

Auth::start();

if (!Auth::hasSetupAccess()) {
    $redirect = asset('abbonamenti.php?upgrade=setup');
    header('Location: ' . $redirect);
    exit;
}

$hotlapId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($hotlapId <= 0) {
    http_response_code(404);
    echo 'Setup non trovato';
    exit;
}

$pdo = Database::pdo();
$st = $pdo->prepare(
    'SELECT h.setup_file, c.name AS car_name, t.name AS track_name
     FROM hotlaps h
     JOIN cars c ON c.id = h.car_id
     JOIN tracks t ON t.id = h.track_id
     WHERE h.id = ? LIMIT 1'
);
$st->execute([$hotlapId]);
$row = $st->fetch();

if (!$row || empty($row['setup_file'])) {
    http_response_code(404);
    echo 'Setup non disponibile per questo hotlap';
    exit;
}

$storageRoot = realpath(__DIR__ . '/../storage/setups');
$fullPath = realpath(__DIR__ . '/../' . ltrim($row['setup_file'], '/'));

if (!$storageRoot || !$fullPath || strpos($fullPath, $storageRoot) !== 0 || !is_file($fullPath)) {
    http_response_code(404);
    echo 'File di setup non trovato sul server';
    exit;
}

$extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
$slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($row['car_name'] . '-' . $row['track_name']));
$slug = trim($slug, '-');
$downloadName = $slug ? $slug . ($extension ? '.' . $extension : '') : basename($fullPath);
$filesize = filesize($fullPath);

$mime = $extension === 'json' ? 'application/json' : 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
if ($filesize !== false) {
    header('Content-Length: ' . $filesize);
}
readfile($fullPath);
exit;
