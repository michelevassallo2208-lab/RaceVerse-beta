<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
if (!Auth::isAdmin()) { header('Location: /login.php'); exit; }
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RaceVerse • Admin Control</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0f1117] text-gray-100 min-h-screen">
  <div class="max-w-6xl mx-auto px-6 py-10 space-y-8">
    <header class="rounded-3xl p-8 bg-gradient-to-r from-amber-500/30 via-orange-500/10 to-emerald-500/10 border border-white/10 shadow-2xl">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-amber-100/70 mb-2">RaceVerse Admin Suite</p>
          <h1 class="text-3xl md:text-4xl font-black">Centro di controllo</h1>
          <p class="text-sm text-amber-100/80 mt-2">Gestisci contenuti, utenti e asset del servizio. Ogni modifica viene sincronizzata in tempo reale con il front-end pubblico.</p>
        </div>
        <div class="rounded-2xl bg-black/40 border border-amber-300/40 px-5 py-4 text-right">
          <div class="text-xs uppercase tracking-[0.3em] text-amber-200">Admin</div>
          <div class="font-semibold text-lg"><?= htmlspecialchars($user['email']) ?></div>
          <a href="/" class="text-sm text-amber-100/70 underline">← Torna al sito</a>
        </div>
      </div>
    </header>

    <section class="grid gap-6 lg:grid-cols-3">
      <div class="rounded-3xl border border-white/15 bg-black/40 p-6">
        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Database cars</div>
        <h2 class="text-xl font-semibold mt-3">Auto & categorie</h2>
        <p class="text-sm text-white/70 mt-2">Aggiungi nuove vetture per Le Mans Ultimate, iRacing e ACC. Collega immagini e categorie per alimentare le raccomandazioni.</p>
        <a href="#" class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white text-black text-sm font-semibold">Gestisci garage</a>
      </div>
      <div class="rounded-3xl border border-white/15 bg-black/40 p-6">
        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Hotlap intelligence</div>
        <h2 class="text-xl font-semibold mt-3">Classifiche e tempi</h2>
        <p class="text-sm text-white/70 mt-2">Aggiorna i record dei pro-player, definisci la vettura dominante per ogni pista e pubblica analisi meta.</p>
        <a href="#" class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-400 text-black text-sm font-semibold">Gestisci hotlap</a>
      </div>
      <div class="rounded-3xl border border-white/15 bg-black/40 p-6">
        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Premium setups</div>
        <h2 class="text-xl font-semibold mt-3">Assetti</h2>
        <p class="text-sm text-white/70 mt-2">Carica i file assetto aggiornati, separa configurazioni qualifica/gara e associa note tecniche.</p>
        <a href="#" class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-purple-400 text-black text-sm font-semibold">Gestisci assetti</a>
      </div>
    </section>

    <section class="rounded-3xl border border-white/15 bg-black/50 p-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
          <h2 class="text-2xl font-bold">Gestione community</h2>
          <p class="text-sm text-white/70 mt-2">Crea nuovi profili, assegna ruoli (Admin, RaceVerse Pro, RaceVerse Guest) e abilita manualmente i piani di abbonamento.</p>
        </div>
        <a href="#" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-amber-300 text-black text-sm font-semibold">Gestisci utenti</a>
      </div>
      <div class="mt-6 grid gap-4 md:grid-cols-3 text-sm text-white/70">
        <div class="p-4 rounded-2xl border border-white/10 bg-black/40">• Invita nuovi piloti o staff</div>
        <div class="p-4 rounded-2xl border border-white/10 bg-black/40">• Upgrade/downgrade dei ruoli</div>
        <div class="p-4 rounded-2xl border border-white/10 bg-black/40">• Attiva o sospendi abbonamenti</div>
      </div>
    </section>
  </div>
</body>
</html>
