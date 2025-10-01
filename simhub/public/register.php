<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstName = trim($_POST['first_name'] ?? '');
  $lastName = trim($_POST['last_name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $emailConfirm = strtolower(trim($_POST['email_confirm'] ?? ''));
  $password = $_POST['password'] ?? '';
  $passwordConfirm = $_POST['password_confirm'] ?? '';

  if (!$firstName) { $errors['first_name'] = 'Inserisci il tuo nome.'; }
  if (!$lastName) { $errors['last_name'] = 'Inserisci il tuo cognome.'; }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email non valida.';
  }
  if ($email !== $emailConfirm) {
    $errors['email_confirm'] = 'Le email devono coincidere.';
  }
  if (strlen($password) < 8) {
    $errors['password'] = 'La password deve contenere almeno 8 caratteri.';
  }
  if ($password !== $passwordConfirm) {
    $errors['password_confirm'] = 'Le password devono coincidere.';
  }

  if (!$errors) {
    try {
      $pdo = Database::pdo();
      $existing = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
      $existing->execute([$email]);
      if ($existing->fetch()) {
        $errors['email'] = 'Questa email è già registrata. Prova ad accedere.';
      } else {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, verification_token, subscription_plan, subscription_active) VALUES (?, ?, ?, ?, ?, ?, 0)');
        $stmt->execute([
          $email,
          password_hash($password, PASSWORD_DEFAULT),
          $firstName,
          $lastName,
          $token,
          'RaceVerse PRO'
        ]);
        $success = true;
      }
    } catch (PDOException $e) {
      $errors['general'] = 'Impossibile completare la registrazione. Verifica la connessione al database.';
    }
  }
}

include __DIR__ . '/../templates/header.php';
?>
<section class="max-w-4xl mx-auto rounded-3xl p-10 bg-black/40 border border-white/10 shadow-2xl shadow-emerald-500/20">
  <div class="grid md:grid-cols-2 gap-10 items-start">
    <div class="space-y-5">
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-br from-emerald-400 via-teal-400 to-cyan-400">
          <img src="<?= asset('assets/images/logo.png') ?>" class="w-8 h-8" alt="logo RaceVerse">
        </span>
        <h1 class="text-3xl font-bold">Unisciti a RaceVerse PRO</h1>
      </div>
      <p class="text-white/70 text-sm leading-relaxed">
        L'abbonamento RaceVerse PRO costa <strong>€2,99 al mese</strong> e ti dà accesso a report puliti, setup condivisi e supporto rapido.
        Dopo la registrazione riceverai un'email con il link di conferma per attivare l'account.
      </p>
      <ul class="space-y-2 text-white/75 text-sm">
        <li>• Dashboard personale con storico hotlap e note setup.</li>
        <li>• Aggiornamenti mirati dagli eventi endurance e sprint.</li>
        <li>• Supporto prioritario via email con il team RaceVerse.</li>
      </ul>
      <p class="text-xs text-white/50">Imposta il tuo server SMTP per inviare automaticamente l'email di conferma.</p>
    </div>
    <div class="p-6 rounded-2xl bg-white/5 border border-white/10">
      <?php if ($success): ?>
        <div class="p-4 rounded-xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 text-sm mb-4">
          Registrazione completata! Controlla la posta per confermare l'indirizzo e-mail e attivare RaceVerse PRO.
        </div>
        <a href="<?= asset('login.php') ?>" class="inline-flex px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Vai al login</a>
      <?php else: ?>
        <?php if (!empty($errors['general'])): ?>
          <div class="p-4 rounded-xl bg-red-500/20 border border-red-400/40 text-red-100 text-sm mb-4"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm mb-1 text-white/70" for="first_name">Nome</label>
              <input id="first_name" name="first_name" type="text" value="<?= htmlspecialchars($firstName ?? '') ?>" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 focus:outline-none focus:border-emerald-400" required>
              <?php if (!empty($errors['first_name'])): ?><p class="text-xs text-red-300 mt-1"><?= htmlspecialchars($errors['first_name']) ?></p><?php endif; ?>
            </div>
            <div>
              <label class="block text-sm mb-1 text-white/70" for="last_name">Cognome</label>
              <input id="last_name" name="last_name" type="text" value="<?= htmlspecialchars($lastName ?? '') ?>" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 focus:outline-none focus:border-emerald-400" required>
              <?php if (!empty($errors['last_name'])): ?><p class="text-xs text-red-300 mt-1"><?= htmlspecialchars($errors['last_name']) ?></p><?php endif; ?>
            </div>
          </div>
          <div>
            <label class="block text-sm mb-1 text-white/70" for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= htmlspecialchars($email ?? '') ?>" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 focus:outline-none focus:border-emerald-400" required>
            <?php if (!empty($errors['email'])): ?><p class="text-xs text-red-300 mt-1"><?= htmlspecialchars($errors['email']) ?></p><?php endif; ?>
          </div>
          <div>
            <label class="block text-sm mb-1 text-white/70" for="email_confirm">Conferma email</label>
            <input id="email_confirm" name="email_confirm" type="email" value="<?= htmlspecialchars($emailConfirm ?? '') ?>" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 focus:outline-none focus:border-emerald-400" required>
            <?php if (!empty($errors['email_confirm'])): ?><p class="text-xs text-red-300 mt-1"><?= htmlspecialchars($errors['email_confirm']) ?></p><?php endif; ?>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm mb-1 text-white/70" for="password">Password</label>
              <input id="password" name="password" type="password" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 focus:outline-none focus:border-emerald-400" required>
              <?php if (!empty($errors['password'])): ?><p class="text-xs text-red-300 mt-1"><?= htmlspecialchars($errors['password']) ?></p><?php endif; ?>
            </div>
            <div>
              <label class="block text-sm mb-1 text-white/70" for="password_confirm">Conferma password</label>
              <input id="password_confirm" name="password_confirm" type="password" class="w-full p-3 rounded-xl bg-black/40 border border-white/20 focus:outline-none focus:border-emerald-400" required>
              <?php if (!empty($errors['password_confirm'])): ?><p class="text-xs text-red-300 mt-1"><?= htmlspecialchars($errors['password_confirm']) ?></p><?php endif; ?>
            </div>
          </div>
          <button class="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Completa registrazione</button>
          <p class="text-xs text-white/50 text-center">Registrandoti accetti i termini di utilizzo di RaceVerse PRO. L'abbonamento si rinnova di mese in mese a €2,99 finché non disdici.</p>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
