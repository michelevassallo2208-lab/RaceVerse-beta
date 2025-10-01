<?php
require_once __DIR__ . '/../src/helpers.php';
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
    <a href="<?= asset('index.php') ?>" class="flex items-center gap-3 group">
      <span class="relative inline-flex">
        <span class="absolute inset-0 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-emerald-500 blur group-hover:opacity-80 opacity-60 transition"></span>
        <img src="<?= asset('assets/images/logo.png') ?>" class="w-10 h-10 relative" alt="logo">
      </span>
      <span class="text-xl font-extrabold tracking-wide">RaceVerse</span>
    </a>
    <nav class="flex items-center gap-1 text-sm">
      <a href="<?= asset('index.php') ?>" class="nav-link">Home</a>
      <a href="<?= asset('index.php') ?>#selector" class="nav-link">Hotlaps</a>
      <a href="<?= asset('index.php') ?>#pro" class="nav-link">RaceVerse PRO</a>
      <a href="<?= asset('payment.php') ?>" class="nav-link">Abbonamento</a>
      <a href="<?= asset('register.php') ?>" class="nav-link">Registrati</a>
      <a href="<?= asset('login.php') ?>" class="ml-3 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-purple-500/30 transition">Accedi</a>
    </nav>
  </div>
</header>
<main class="max-w-7xl mx-auto px-4 md:px-6 py-10 space-y-12">
