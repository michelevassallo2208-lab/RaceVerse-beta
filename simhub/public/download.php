<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

Auth::start();

if (!Auth::isPro()) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="it"><head><meta charset="utf-8"><title>MetaVerse Pro richiesto</title>';
    echo '<style>body{font-family:system-ui;background:#0f1117;color:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}';
    echo '.card{background:rgba(15,17,23,.85);border:1px solid rgba(255,255,255,.1);padding:2.5rem;border-radius:1.5rem;max-width:420px;text-align:center;}';
    echo '.card a{color:#38bdf8;text-decoration:none;font-weight:600;}</style></head><body>';
    echo '<div class="card"><h1 style="font-size:1.5rem;margin-bottom:0.75rem;">MetaVerse Pro richiesto</h1>';
    echo '<p style="margin-bottom:1.25rem;line-height:1.6;">Il download degli assetti è riservato agli abbonati MetaVerse Pro.</p>';
    echo '<a href="/account.php">Vai al tuo account</a></div></body></html>';
    exit;
}

$hotlapId = isset($_GET['hotlap']) ? (int)$_GET['hotlap'] : 0;
if ($hotlapId <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Hotlap non valida.";
    exit;
}

$pdo = Database::pdo();
$sql = "SELECT h.id, h.driver, h.lap_time_ms, h.recorded_at, c.name AS car_name, t.name AS track_name
        FROM hotlaps h
        JOIN cars c ON c.id = h.car_id
        JOIN tracks t ON t.id = h.track_id
        WHERE h.id = ?
        LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$hotlapId]);
$row = $st->fetch();

if (!$row) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Hotlap non trovata.";
    exit;
}

$slugify = static function (string $value): string {
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'setup';
};

$filenameParts = [
    'setup',
    $slugify($row['track_name']),
    $slugify($row['car_name']),
    'hotlap-' . $row['id'],
];
$filename = implode('-', array_filter($filenameParts)) . '.txt';

$contentLines = [
    'MetaVerse Pro – Assetto placeholder',
    '----------------------------------',
    'Pista: ' . $row['track_name'],
    'Auto: ' . $row['car_name'],
    'Pilota: ' . ($row['driver'] ?: 'N/D'),
    'Tempo: ' . $row['lap_time_ms'] . ' ms',
    'Registrato il: ' . ($row['recorded_at'] ?: 'N/D'),
    '',
    'Sostituisci questo file con il setup reale.',
];

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('X-MetaSim-Hotlap-Id: ' . $row['id']);
echo implode(PHP_EOL, $contentLines);
