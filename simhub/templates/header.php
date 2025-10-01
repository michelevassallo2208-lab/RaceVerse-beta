<?php
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$currentUser = Auth::user();
$isAdmin = Auth::isAdmin();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Raceverse</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?= asset('assets/images/logo.png') ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="bg-[#0f1117] text-gray-100">
<header class="sticky top-0 z-50 bg-[#0f1117]/70 backdrop-blur border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="<?= asset('index.php') ?>" class="flex items-center gap-3">
      <img src="<?= asset('assets/images/logo.png') ?>" class="w-12 h-12 drop-shadow-lg" alt="Raceverse logo">
      <span class="text-2xl font-extrabold tracking-tight">Raceverse</span>
    </a>
    <nav class="flex items-center gap-2 text-sm">
      <a href="<?= asset('index.php') ?>" class="px-3 py-2 hover:underline decoration-2">Home</a>
      <?php if ($currentUser): ?>
        <a href="<?= asset('dashboard.php') ?>" class="px-3 py-2 hover:underline decoration-2">Dashboard</a>
        <a href="<?= asset('dashboard.php#hotlaps') ?>" class="px-3 py-2 hover:underline decoration-2">Hotlaps</a>
        <a href="<?= asset('dashboard.php#setups') ?>" class="px-3 py-2 hover:underline decoration-2">Setups</a>
        <a href="<?= asset('account.php') ?>" class="px-3 py-2 hover:underline decoration-2">Account</a>
        <?php if ($isAdmin): ?>
          <a href="<?= asset('admin/index.php') ?>" class="px-3 py-2 hover:underline decoration-2">Admin</a>
        <?php endif; ?>
        <a href="<?= asset('logout.php') ?>" class="ml-2 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20">Logout</a>
      <?php else: ?>
        <a href="<?= asset('register.php') ?>" class="ml-2 px-4 py-2 rounded-lg bg-white text-black font-semibold hover:bg-white/90">Registrati</a>
        <a href="<?= asset('login.php') ?>" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20">Accedi</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="max-w-7xl mx-auto px-4 md:px-6 py-8">
