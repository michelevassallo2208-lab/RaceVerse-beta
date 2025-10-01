<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
    header('Location: /admin/index.php'); exit;
  } else {
    $errCode = Auth::lastError();
    $error = $errCode === 'unverified'
      ? 'L\'account non è ancora verificato. Completa la conferma via email.'
      : 'Credenziali non valide';
  }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="hero-gradient min-h-screen flex items-center justify-center">
  <form method="post" class="glass border border-white/10 p-8 rounded-2xl w-[380px] max-w-[92vw]">
    <div class="flex items-center gap-3 mb-6">
      <img src="/assets/images/logo.png" class="w-14 h-14 drop-shadow-lg" alt="Raceverse logo">
      <h1 class="text-xl font-bold">Admin • Raceverse</h1>
    </div>
    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-500/15 border border-red-500/25 text-red-200 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <label class="block text-sm mb-1">Email</label>
    <input type="email" name="email" class="w-full p-3 rounded-xl bg-white/5 border border-white/20 mb-3" required>
    <label class="block text-sm mb-1">Password</label>
    <input type="password" name="password" class="w-full p-3 rounded-xl bg-white/5 border border-white/20 mb-4" required>
    <button class="w-full py-3 rounded-xl bg-white text-black font-semibold">Entra</button>
  </form>
</body>
</html>
