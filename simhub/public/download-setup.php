<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$user = Auth::user();
if (!$user) {
    redirect_to('login.php');
}
if (!(Auth::isAdmin() || Auth::isPro())) {
    redirect_to('payment.php');
}
$setupPath = __DIR__ . '/assets/files/raceverse-setup.txt';
if (!is_file($setupPath)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'Setup non disponibile al momento.';
    exit;
}
header('Content-Description: File Transfer');
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="raceverse-setup.txt"');
header('Content-Length: ' . filesize($setupPath));
readfile($setupPath);
exit;
