<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
if (!Auth::isAdmin()) { header('Location: /login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin • Raceverse</title>
<script src="https://cdn.tailwindcss.com"></script>
</head><body class="bg-gray-100">
<div class="max-w-5xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Pannello Admin</h1>
  <p class="mb-6 text-sm text-gray-600">Gestione contenuti (visibile solo con ruolo admin).</p>
  <a href="/" class="underline">← Torna al sito</a>
</div>
</body></html>
