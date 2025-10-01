<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Mailer.php';

Auth::start();
if (Auth::user()) {
    header('Location: /account.php');
    exit;
}

$error = null;
$success = false;
$pdo = null;

$values = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['first_name'] = trim($_POST['first_name'] ?? '');
    $values['last_name'] = trim($_POST['last_name'] ?? '');
    $values['email'] = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($values['first_name'] === '' || $values['last_name'] === '') {
        $error = 'Inserisci nome e cognome.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Inserisci un indirizzo email valido.';
    } elseif (strlen($password) < 8) {
        $error = 'La password deve contenere almeno 8 caratteri.';
    } else {
        try {
            $pdo = Database::pdo();
            $pdo->beginTransaction();

            $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->execute([$values['email']]);
            if ($check->fetch()) {
                $error = 'Esiste già un account con questa email.';
                $pdo->rollBack();
            } else {
                $token = bin2hex(random_bytes(32));
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, verification_token) VALUES (?, ?, ?, ?, ?)');
                $insert->execute([
                    $values['email'],
                    $hash,
                    $values['first_name'],
                    $values['last_name'],
                    $token,
                ]);

                Mailer::sendVerificationEmail($values['email'], $values['first_name'], $values['last_name'], $token);
                $pdo->commit();
                $success = true;
            }
        } catch (Throwable $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Registration error: ' . $e->getMessage());
            $error = 'Si è verificato un errore durante la registrazione. Riprova più tardi.';
        }
    }
}

include __DIR__ . '/../templates/header.php';
?>
<section class="max-w-2xl mx-auto rounded-2xl p-6 md:p-10 bg-white/5 border border-white/10">
  <div class="flex items-center gap-4 mb-6">
    <img src="<?= asset('assets/images/logo.png') ?>" class="w-16 h-16 drop-shadow-lg" alt="Raceverse logo">
    <div>
      <h1 class="text-3xl font-bold">Crea il tuo account</h1>
      <p class="text-sm text-white/60">Registrati per accedere ai dati e alle funzionalità di Raceverse.</p>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="mb-6 p-4 rounded-xl bg-emerald-600/15 border border-emerald-500/30 text-emerald-100">
      <h2 class="text-lg font-semibold mb-1">Registrazione completata!</h2>
      <p class="text-sm">Ti abbiamo inviato un'email di benvenuto da <strong>noreply@raceverse.it</strong>. Clicca sul link di conferma per attivare il tuo account.</p>
    </div>
  <?php else: ?>
    <?php if ($error): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-600/20 border border-red-500/30 text-red-100 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Nome</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($values['first_name']) ?>" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Cognome</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($values['last_name']) ?>" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" required>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($values['email']) ?>" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" required>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm mb-1">Password</label>
        <input type="password" name="password" class="w-full p-3 rounded-xl bg-white/5 border border-white/20" required>
        <p class="mt-1 text-xs text-white/50">Minimo 8 caratteri.</p>
      </div>
      <div class="md:col-span-2 flex justify-end">
        <button class="px-6 py-3 rounded-xl bg-white text-black font-semibold hover:bg-white/90">Crea account</button>
      </div>
    </form>
  <?php endif; ?>

  <p class="mt-6 text-sm text-white/60">Hai già un account? <a href="<?= asset('login.php') ?>" class="text-white hover:underline">Accedi</a></p>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
