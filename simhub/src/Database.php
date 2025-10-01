<?php
class Database {
  public static function pdo(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = require __DIR__.'/config.php';
    $dsn = "mysql:host={$c['db']['host']};dbname={$c['db']['dbname']};charset={$c['db']['charset']}";
    $pdo = new PDO($dsn, $c['db']['user'], $c['db']['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
  }
}
