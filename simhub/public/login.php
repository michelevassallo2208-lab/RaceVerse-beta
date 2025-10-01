<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/helpers.php';
Auth::start();
$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (Auth::login($_POST['email']??'', $_POST['password']??'')) {
    redirect_to('account.php');
  } else { $error='Credenziali non valide'; }
}
include __DIR__ . '/../templates/header.php';
?>
<section class="max-w-2xl mx-auto grid md:grid-cols-2 gap-8 items-center rounded-3xl p-8 bg-black/40 border border-white/10 shadow-2xl shadow-indigo-500/20">
  <div class="space-y-4">
    <div class="flex items-center gap-3">
      <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500">
        <img src="<?= asset('assets/images/logo.png') ?>" class="w-8 h-8" alt="logo">
      </span>
      <h1 class="text-2xl font-bold">Area Riservata</h1>
    </div>
    <p class="text-sm text-white/70">Accedi per sincronizzare i tuoi hotlap, salvare i setup e sbloccare l'accesso RaceVerse PRO.</p>
    <ul class="space-y-2 text-sm text-white/70">
      <li>• Dashboard personale con i tuoi record</li>
      <li>• Aggiornamenti mirati e anteprime assetti</li>
      <li>• Supporto prioritario con il team RaceVerse</li>
    </ul>
  </div>
  <div class="p-6 rounded-2xl bg-white/5 border border-white/10">
    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded-xl bg-red-500/20 border border-red-500/40 text-red-100 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm mb-1 text-white/70">Email</label>
        <input type="email" name="email" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 focus:outline-none focus:border-indigo-400" required>
      </div>
      <div>
        <label class="block text-sm mb-1 text-white/70">Password</label>
        <input type="password" name="password" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 focus:outline-none focus:border-indigo-400" required>
      </div>
      <button class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30">Entra</button>
      <p class="text-xs text-white/50 text-center">Non hai un account? <a href="<?= asset('register.php') ?>" class="text-emerald-300 hover:text-emerald-200">Attiva RaceVerse PRO</a></p>
    </form>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
