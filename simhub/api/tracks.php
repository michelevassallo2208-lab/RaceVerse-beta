<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../src/Database.php';
$pdo = Database::pdo();
$game = isset($_GET['game']) ? (int)$_GET['game'] : 0;
if (!$game) { echo json_encode([]); exit; }
$st = $pdo->prepare("SELECT id,name FROM tracks WHERE game_id=? ORDER BY name");
$st->execute([$game]);
echo json_encode($st->fetchAll() ?: []);
