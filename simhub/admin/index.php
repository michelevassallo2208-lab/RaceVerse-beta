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
  <h1 class="text-3xl font-bold mb-4">Pannello Admin</h1>
  <p class="mb-6 text-sm text-gray-600">Gestisci utenti, hotlap e setup caricati sulla piattaforma Raceverse.</p>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
    <a href="/admin/accounts.php" class="block p-6 rounded-2xl bg-white shadow border border-gray-200 hover:border-indigo-300 transition">
      <h2 class="text-xl font-semibold mb-2">Gestione account</h2>
      <p class="text-sm text-gray-600">Visualizza utenti registrati, ruoli e stato abbonamenti.</p>
    </a>
    <a href="/admin/hotlaps.php" class="block p-6 rounded-2xl bg-white shadow border border-gray-200 hover:border-indigo-300 transition">
      <h2 class="text-xl font-semibold mb-2">Hotlap & setup</h2>
      <p class="text-sm text-gray-600">Aggiungi nuovi tempi, carica assetti e gestisci i contenuti esistenti.</p>
    </a>
  </div>
  <a href="/dashboard.php" class="underline">← Torna alla dashboard</a>
</div>
</body></html>
