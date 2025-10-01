<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (Auth::login($_POST['email']??'', $_POST['password']??'')) {
    header('Location: /account.php'); exit;
  } else {
    $errCode = Auth::lastError();
    $error = $errCode === 'unverified'
      ? 'Conferma il tuo indirizzo email prima di accedere. Controlla la casella di posta.'
      : 'Credenziali non valide';
  }
}
include __DIR__ . '/../templates/header.php';
?>
<section class="max-w-md mx-auto rounded-2xl p-6 md:p-8 bg-white/5 border border-white/10">
  <div class="flex items-center gap-3 mb-6">
    <img src="<?= asset('assets/images/logo.png') ?>" class="w-14 h-14 drop-shadow-lg" alt="Raceverse logo">
    <div>
      <h1 class="text-2xl font-bold">Accedi</h1>
      <p class="text-xs text-white/60">Bentornato su Raceverse</p>
    </div>
  </div>
  <?php if ($error): ?>
    <div class="mb-4 p-3 rounded bg-red-500/15 border border-red-500/25 text-red-200 text-sm"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-3">
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input type="email" name="email" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Password</label>
      <input type="password" name="password" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" required>
    </div>
    <button class="w-full py-3 rounded-xl bg-white text-black font-semibold">Entra</button>
  </form>
  <p class="mt-4 text-center text-sm text-white/60">Non hai un account? <a href="<?= asset('register.php') ?>" class="text-white hover:underline">Registrati ora</a></p>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
