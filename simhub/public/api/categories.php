<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/Database.php';
$pdo = Database::pdo();
$game = isset($_GET['game']) ? (int)$_GET['game'] : 0;
if (!$game) { echo json_encode([]); exit; }
$sql = "SELECT DISTINCT c.id, c.name
        FROM categories c
        JOIN cars car ON car.category_id = c.id
        WHERE car.game_id = ?
        ORDER BY c.name";
$st = $pdo->prepare($sql);
$st->execute([$game]);
echo json_encode($st->fetchAll() ?: []);
