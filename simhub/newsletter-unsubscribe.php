<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Auth.php';

$token = trim($_GET['token'] ?? '');
$status = 'invalid';
$message = 'Link non valido';
$emailRemoved = null;

if ($token !== '') {
    try {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id, email FROM newsletter_subscriptions WHERE unsubscribe_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $subscription = $stmt->fetch();

        if ($subscription) {
            $emailRemoved = $subscription['email'];
            $delete = $pdo->prepare('DELETE FROM newsletter_subscriptions WHERE id = ?');
            $delete->execute([$subscription['id']]);
            $status = 'success';
            $message = 'Iscrizione annullata con successo';
        } else {
            $status = 'invalid';
            $message = 'Il link è scaduto o l’iscrizione è già stata rimossa.';
        }
    } catch (Throwable $e) {
        error_log('Newsletter unsubscribe error: ' . $e->getMessage());
        $status = 'error';
        $message = 'Impossibile completare la richiesta in questo momento.';
    }
}

include __DIR__ . '/templates/header.php';

$badgeClass = match ($status) {
    'success' => 'bg-emerald-500/20 border-emerald-400/40 text-emerald-200',
    'error' => 'bg-rose-500/20 border-rose-400/40 text-rose-200',
    default => 'bg-indigo-500/20 border-indigo-400/40 text-indigo-200',
};
?>
<section class="rounded-3xl bg-black/40 border border-white/10 shadow-2xl shadow-indigo-500/20 p-6 md:p-10 space-y-6 text-sm md:text-base">
  <div class="space-y-3">
    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border <?= $badgeClass ?> uppercase tracking-[0.2em] text-xs">Newsletter</span>
    <h1 class="text-3xl font-bold text-white">Gestione preferenze</h1>
    <p class="text-white/70 leading-relaxed"><?= htmlspecialchars($message) ?></p>
  </div>

  <?php if ($status === 'success' && $emailRemoved): ?>
    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-white/80">
      <p class="mb-2">L’indirizzo <span class="font-semibold text-white"><?= htmlspecialchars($emailRemoved) ?></span> non riceverà più aggiornamenti dalla newsletter RaceVerse.</p>
      <p class="text-white/60">Se vuoi tornare a bordo in futuro, potrai iscriverti nuovamente dalla homepage o dalle impostazioni account.</p>
    </div>
  <?php elseif ($status === 'invalid'): ?>
    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-white/70 space-y-2">
      <p>Il link potrebbe essere stato utilizzato in precedenza oppure scaduto.</p>
      <p class="text-white/60">Per assistenza scrivici a <a href="mailto:support@raceverse.it" class="text-emerald-300">support@raceverse.it</a> indicando l’indirizzo email interessato.</p>
    </div>
  <?php elseif ($status === 'error'): ?>
    <div class="p-6 rounded-2xl bg-rose-500/10 border border-rose-400/30 text-rose-100">
      Si è verificato un imprevisto con il sistema di newsletter. Riprova tra qualche minuto oppure contattaci su support@raceverse.it.
    </div>
  <?php endif; ?>

  <div class="flex flex-wrap gap-3 items-center">
    <a href="<?= asset('index.php') ?>" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30">Torna alla home RaceVerse</a>
    <a href="mailto:support@raceverse.it" class="text-white/60 hover:text-white transition">Hai domande? Contatta il supporto</a>
  </div>
</section>

<?php include __DIR__ . '/templates/footer.php';
