<?php
require_once __DIR__ . '/../src/helpers.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>MetaSim</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?= asset('assets/images/logo.png') ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="bg-[#0f1117] text-gray-100">
<header class="sticky top-0 z-50 bg-[#0f1117]/70 backdrop-blur border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="<?= asset('index.php') ?>" class="flex items-center gap-3">
      <img src="<?= asset('assets/images/logo.png') ?>" class="w-8 h-8" alt="logo">
      <span class="text-xl font-extrabold">MetaSim</span>
    </a>
    <nav class="flex items-center gap-2 text-sm">
      <a href="<?= asset('index.php') ?>" class="px-3 py-2 hover:underline decoration-2">Home</a>
      <a href="<?= asset('hotlaps.php') ?>" class="px-3 py-2 hover:underline decoration-2">Hotlaps</a>
      <a href="<?= asset('setups.php') ?>" class="px-3 py-2 hover:underline decoration-2">Setups</a>
      <a href="<?= asset('login.php') ?>" class="ml-2 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20">Login</a>
    </nav>
  </div>
</header>
<main class="max-w-7xl mx-auto px-4 md:px-6 py-8">
