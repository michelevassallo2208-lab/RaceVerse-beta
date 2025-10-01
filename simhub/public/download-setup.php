<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';
Auth::start();
$user = Auth::user();
if (!$user) {
    redirect_to('login.php');
}
if (!(Auth::isAdmin() || Auth::isPro())) {
    redirect_to('payment.php');
}
$carId = isset($_GET['car']) ? (int)$_GET['car'] : 0;
$trackId = isset($_GET['track']) ? (int)$_GET['track'] : 0;

if (!$carId || !$trackId) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Combinazione auto/pista non trovata.';
    exit;
}

$pdo = Database::pdo();
$sql = "SELECT cs.file_slug, cs.notes, cs.created_at,
               c.name AS car_name,
               t.name AS track_name,
               cat.name AS category_name,
               MIN(h.lap_time_ms) AS best_time
        FROM car_setups cs
        JOIN cars c ON c.id = cs.car_id
        JOIN tracks t ON t.id = cs.track_id
        JOIN categories cat ON cat.id = c.category_id
        LEFT JOIN hotlaps h ON h.car_id = cs.car_id AND h.track_id = cs.track_id
        WHERE cs.car_id = ? AND cs.track_id = ?
        GROUP BY cs.id, cs.file_slug, cs.notes, cs.created_at, c.name, t.name, cat.name";
$stmt = $pdo->prepare($sql);
$stmt->execute([$carId, $trackId]);
$setup = $stmt->fetch();

if (!$setup) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Assetto non disponibile.';
    exit;
}

function slugify(string $value): string {
    $original = $value;
    $converted = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
    if ($converted !== false) {
        $value = $converted;
    }
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
    $value = trim($value, '-');
    return $value !== '' ? $value : 'raceverse-setup-' . substr(md5($original), 0, 6);
}

function fmt_time(?int $ms): string {
    if (!$ms) {
        return 'N/D';
    }
    $minutes = floor($ms / 60000);
    $seconds = floor(($ms % 60000) / 1000);
    $millis  = $ms % 1000;
    return sprintf('%d:%02d.%03d', $minutes, $seconds, $millis);
}

$lines = [
    'RaceVerse PRO - Setup ufficiale',
    '--------------------------------',
    'Auto: ' . $setup['car_name'],
    'Categoria: ' . $setup['category_name'],
    'Pista: ' . $setup['track_name'],
    'Lap di riferimento: ' . fmt_time($setup['best_time']),
    'Aggiornato il: ' . ($setup['created_at'] ? date('d/m/Y H:i', strtotime($setup['created_at'])) : date('d/m/Y H:i')),
    '',
    $setup['notes'] ?: 'Note non disponibili: contatta il team RaceVerse per dettagli aggiuntivi.',
    '',
    'File ID: ' . $setup['file_slug'],
    'Grazie per supportare RaceVerse PRO.'
];

$content = implode("\n", $lines);
$filename = slugify($setup['track_name']) . '-' . slugify($setup['car_name']) . '-' . $setup['file_slug'] . '.txt';

header('Content-Description: File Transfer');
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($content));
echo $content;
exit;
