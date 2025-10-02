<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$currentUser = Auth::user();
$baseUrl = asset('');
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>RaceVerse</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?= asset('assets/images/logo.png') ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="bg-[#06070d] text-gray-100 premium-texture" data-base-url="<?= htmlspecialchars(rtrim($baseUrl, '/')) ?>">
<header class="sticky top-0 z-50 bg-[#06070d]/80 backdrop-blur-2xl border-b border-white/10 shadow-lg shadow-indigo-500/10">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="<?= asset('index.php') ?>" class="flex items-center gap-4 group" aria-label="RaceVerse home">
      <span class="relative inline-flex">
        <span class="absolute inset-0 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-emerald-500 blur-3xl group-hover:opacity-80 opacity-70 transition"></span>
        <span class="absolute inset-0 rounded-full border border-white/10 group-hover:border-white/40 transition"></span>
        <img src="<?= asset('assets/images/logo.png') ?>" class="relative w-14 h-14 md:w-16 md:h-16 drop-shadow-[0_0_18px_rgba(99,102,241,0.45)]" alt="Logo RaceVerse">
      </span>
      <span class="flex flex-col leading-none">
        <span class="text-2xl md:text-3xl font-black tracking-[0.3em] uppercase text-transparent bg-clip-text bg-gradient-to-r from-white via-emerald-200 to-indigo-200 drop-shadow-[0_0_12px_rgba(255,255,255,0.35)]">RaceVerse</span>
        <span class="text-[0.7rem] md:text-xs font-semibold text-emerald-200/90 tracking-[0.4em] uppercase">Pro Racing Hub</span>
      </span>
    </a>
    <nav class="flex items-center gap-1 text-sm">
      <a href="<?= asset('index.php') ?>" class="nav-link">Home</a>
      <a href="<?= asset('payment.php') ?>" class="nav-link">Accesso PRO</a>
      <a href="<?= $currentUser ? asset('support.php') : asset('support-guest.php') ?>" class="nav-link">Supporto</a>
      <?php if ($currentUser): ?>
        <a href="<?= asset('account.php') ?>" class="ml-3 px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30 hover:shadow-cyan-500/30 transition">Dashboard</a>
      <?php else: ?>
        <a href="<?= asset('register.php') ?>" class="nav-link">Registrati</a>
        <a href="<?= asset('login.php') ?>" class="ml-3 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-purple-500/30 transition">Accedi</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="max-w-7xl mx-auto px-4 md:px-6 py-10 space-y-12">
