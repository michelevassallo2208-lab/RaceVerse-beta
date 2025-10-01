<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/helpers.php';
Auth::start();
if (!Auth::isAdmin()) { redirect_to('login.php'); }
?>
<!DOCTYPE html>
<html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin • RaceVerse</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="<?= asset('../public/assets/css/style.css') ?>">
</head><body class="bg-[#05060b] text-white premium-texture min-h-screen flex items-center justify-center">
<div class="max-w-4xl w-full mx-auto p-8">
  <div class="rounded-3xl bg-black/60 border border-white/10 shadow-2xl shadow-indigo-500/20 p-10 space-y-6">
    <div class="flex items-center gap-4">
      <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-emerald-500">
        <img src="<?= asset('../public/assets/images/logo.png') ?>" class="w-7 h-7" alt="logo">
      </span>
      <div>
        <h1 class="text-3xl font-bold">Pannello RaceVerse</h1>
        <p class="text-sm text-white/60">Gestisci giochi, categorie, piste e tempi direttamente dal database.</p>
      </div>
    </div>
    <div class="grid md:grid-cols-2 gap-4 text-sm text-white/70">
      <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
        <h2 class="font-semibold text-white mb-2">Cosa puoi fare</h2>
        <ul class="space-y-2">
          <li>• Inserisci nuovi hotlap e aggiorna i record</li>
          <li>• Carica immagini auto per migliorare la vetrina</li>
          <li>• Gestisci l'accesso RaceVerse PRO degli utenti</li>
        </ul>
      </div>
      <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
        <h2 class="font-semibold text-white mb-2">Risorse rapide</h2>
        <a href="<?= asset('../public/index.php') ?>" class="block px-4 py-3 mb-2 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold text-center">← Torna al sito</a>
        <a href="<?= asset('../public/logout.php') ?>" class="block px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white/80 hover:text-white text-center">Logout</a>
      </div>
    </div>
  </div>
</div>
</body></html>
