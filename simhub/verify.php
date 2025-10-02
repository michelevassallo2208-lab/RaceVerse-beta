<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/helpers.php';

$token = strtolower(trim($_GET['token'] ?? ''));
$token = preg_replace('/[^a-f0-9]/', '', $token);
$status = 'error';
$message = "Token non valido. Richiedi una nuova email di conferma.";

if ($token) {
  try {
    $pdo = Database::pdo();
    $st = $pdo->prepare('SELECT id, email, email_verified_at, subscription_active, subscription_plan FROM users WHERE verification_token = ? LIMIT 1');
    $st->execute([$token]);
    $user = $st->fetch();
    if ($user) {
      if (!empty($user['email_verified_at'])) {
        $status = 'info';
        $message = 'Questa email è già stata verificata. Puoi accedere alla tua area RaceVerse.';
      } else {
        $update = $pdo->prepare('UPDATE users SET email_verified_at = NOW(), verification_token = NULL, subscription_plan = COALESCE(subscription_plan, "RaceVerse BASIC") WHERE id = ?');
        $update->execute([$user['id']]);
        $status = 'success';
        $message = 'Email verificata con successo! Il tuo account RaceVerse BASIC è attivo. Puoi acquistare un pass PRO in qualsiasi momento dalla pagina Accesso PRO.';
      }
    } else {
      $message = 'Token di verifica non trovato o già utilizzato.';
    }
  } catch (PDOException $e) {
    $message = 'Errore nella verifica. Riprova più tardi o contatta il supporto.';
  }
}

include __DIR__ . '/templates/header.php';
?>
<section class="max-w-3xl mx-auto rounded-3xl p-10 bg-black/40 border border-white/10 shadow-2xl shadow-emerald-500/20 text-center space-y-6">
  <div class="inline-flex items-center justify-center w-16 h-16 rounded-full <?= $status === 'success' ? 'bg-emerald-500/20 border border-emerald-400/50 text-emerald-200' : ($status === 'info' ? 'bg-indigo-500/20 border border-indigo-400/50 text-indigo-200' : 'bg-red-500/20 border border-red-400/50 text-red-200') ?>">
    <?php if ($status === 'success'): ?>
      ✓
    <?php elseif ($status === 'info'): ?>
      ℹ️
    <?php else: ?>
      !
    <?php endif; ?>
  </div>
  <h1 class="text-3xl font-bold">Verifica account RaceVerse</h1>
  <p class="text-white/70 text-lg">
    <?= htmlspecialchars($message) ?>
  </p>
  <div class="flex flex-col sm:flex-row gap-3 justify-center">
    <a href="<?= asset('login.php') ?>" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Vai al login</a>
    <a href="<?= asset('payment.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Scopri RaceVerse PRO</a>
    <a href="<?= asset('index.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 text-white/80 hover:text-white">Torna alla home</a>
  </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
