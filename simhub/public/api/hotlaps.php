<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/Database.php';
$pdo = Database::pdo();

$game = isset($_GET['game']) ? (int)$_GET['game'] : 0;
$cat  = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$trk  = isset($_GET['track']) ? (int)$_GET['track'] : 0;

if (!$game || !$cat || !$trk) { echo json_encode([]); exit; }

$sql = "SELECT h.id, h.lap_time_ms, h.driver, h.recorded_at,
               c.name AS car_name, c.image_path AS car_image
        FROM hotlaps h
        JOIN cars c ON c.id = h.car_id
        WHERE h.game_id = ? AND h.category_id = ? AND h.track_id = ?
        ORDER BY h.lap_time_ms ASC
        LIMIT 50";
$st = $pdo->prepare($sql);
$st->execute([$game, $cat, $trk]);
$rows = $st->fetchAll();
echo json_encode($rows ?: []);
