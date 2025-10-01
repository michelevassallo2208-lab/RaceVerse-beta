<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/helpers.php';

$pdo = Database::pdo();
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$status = 'error';
$message = 'Link di conferma non valido.';

if ($token && $email) {
    $st = $pdo->prepare('SELECT id, email_verified_at FROM users WHERE email = ? AND verification_token = ? LIMIT 1');
    $st->execute([$email, $token]);
    $user = $st->fetch();

    if ($user) {
        if (!empty($user['email_verified_at'])) {
            $status = 'info';
            $message = 'Il tuo account è già attivo. Puoi accedere con le tue credenziali.';
        } else {
            $upd = $pdo->prepare('UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE id = ?');
            $upd->execute([$user['id']]);
            $status = 'success';
            $message = 'Registrazione confermata! Ora puoi accedere a Raceverse.';
        }
    }
}

include __DIR__ . '/../templates/header.php';
?>
<section class="max-w-xl mx-auto rounded-2xl p-6 md:p-10 bg-white/5 border border-white/10">
  <div class="flex items-center gap-4 mb-6">
    <img src="<?= asset('assets/images/logo.png') ?>" class="w-16 h-16 drop-shadow-lg" alt="Raceverse logo">
    <div>
      <h1 class="text-3xl font-bold">Conferma iscrizione</h1>
      <p class="text-sm text-white/60">Completa l'attivazione del tuo profilo.</p>
    </div>
  </div>

  <?php if ($status === 'success'): ?>
    <div class="p-4 rounded-xl bg-emerald-600/15 border border-emerald-500/30 text-emerald-100">
      <p class="font-semibold mb-1">Tutto pronto!</p>
      <p class="text-sm"><?= htmlspecialchars($message) ?></p>
      <a href="<?= asset('login.php') ?>" class="inline-flex mt-4 px-5 py-2 rounded-lg bg-white text-black font-semibold">Vai al login</a>
    </div>
  <?php elseif ($status === 'info'): ?>
    <div class="p-4 rounded-xl bg-blue-500/15 border border-blue-400/30 text-blue-100">
      <p class="font-semibold mb-1">Account già attivo</p>
      <p class="text-sm"><?= htmlspecialchars($message) ?></p>
      <a href="<?= asset('login.php') ?>" class="inline-flex mt-4 px-5 py-2 rounded-lg bg-white/90 text-black font-semibold">Accedi</a>
    </div>
  <?php else: ?>
    <div class="p-4 rounded-xl bg-red-600/20 border border-red-500/30 text-red-100">
      <p class="font-semibold mb-1">Ops!</p>
      <p class="text-sm"><?= htmlspecialchars($message) ?></p>
      <p class="text-xs mt-3 text-red-100/80">Se il problema persiste contatta l'assistenza.</p>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
