<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/helpers.php';
Auth::start();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
    redirect_to('index.php');
  } else { $error = 'Credenziali non valide'; }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="<?= asset('../public/assets/css/style.css') ?>">
</head>
<body class="hero-gradient premium-texture min-h-screen flex items-center justify-center px-4">
  <form method="post" class="glass border border-white/10 p-8 rounded-3xl w-[420px] max-w-[100%] shadow-2xl shadow-indigo-500/30">
    <div class="flex items-center gap-3 mb-6">
      <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-emerald-500">
        <img src="<?= asset('../public/assets/images/logo.png') ?>" class="w-8 h-8" alt="logo">
      </span>
      <h1 class="text-xl font-bold">Admin • RaceVerse</h1>
    </div>
    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-500/15 border border-red-500/25 text-red-200 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <label class="block text-sm mb-1">Email</label>
    <input type="email" name="email" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 mb-3 focus:outline-none focus:border-indigo-400" required>
    <label class="block text-sm mb-1">Password</label>
    <input type="password" name="password" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 mb-4 focus:outline-none focus:border-indigo-400" required>
    <button class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30">Entra</button>
  </form>
</body>
</html>
