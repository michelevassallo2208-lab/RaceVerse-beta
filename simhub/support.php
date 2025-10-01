<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/TicketService.php';

Auth::start();
$user = Auth::user();
if (!$user) {
    redirect_to('login.php');
}

$messages = ['success' => [], 'error' => []];
$activeTab = $_GET['tab'] ?? 'create';
$selectedCode = $_GET['code'] ?? null;
$selectedTicket = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'open_ticket') {
            $email = trim($_POST['email'] ?? $user['email']);
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['message'] ?? '');
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $messages['error'][] = 'Inserisci un indirizzo email valido.';
            }
            if ($subject === '') {
                $messages['error'][] = 'Indica un oggetto per il ticket.';
            }
            if ($body === '') {
                $messages['error'][] = 'Descrivi la tua richiesta di assistenza.';
            }
            if (empty($messages['error'])) {
                $ticket = TicketService::createTicket((int)$user['id'], $email, $subject, $body);
                if ($ticket) {
                    $messages['success'][] = 'Ticket creato con successo! Il tuo codice è <strong>' . htmlspecialchars($ticket['code']) . '</strong>.';
                    $activeTab = 'timeline';
                    $selectedCode = $ticket['code'];
                    $selectedTicket = $ticket;
                } else {
                    $messages['error'][] = 'Non è stato possibile aprire il ticket al momento. Riprova più tardi.';
                }
            }
        } elseif ($action === 'user_reply') {
            $code = $_POST['code'] ?? '';
            $reply = trim($_POST['reply'] ?? '');
            $activeTab = 'timeline';
            $selectedCode = $code;
            if ($code === '') {
                $messages['error'][] = 'Ticket non valido.';
            } else {
                $ticket = TicketService::getTicketForUser($code, (int)$user['id']);
                if (!$ticket) {
                    $messages['error'][] = 'Ticket non trovato o non associato al tuo account.';
                } elseif (!TicketService::userCanReply($ticket)) {
                    $messages['error'][] = 'Hai già inviato il massimo aggiornamento consentito o il ticket è in attesa del team.';
                    $selectedTicket = $ticket;
                } elseif ($reply === '') {
                    $messages['error'][] = 'Scrivi un messaggio per proseguire la conversazione.';
                    $selectedTicket = $ticket;
                } else {
                    $updated = TicketService::addUserReply($ticket, (int)$user['id'], $reply);
                    if ($updated) {
                        $messages['success'][] = 'Risposta inviata con successo. Attendi un aggiornamento dal team RaceVerse.';
                        $selectedTicket = $updated;
                    } else {
                        $messages['error'][] = 'Non è stato possibile aggiornare il ticket.';
                        $selectedTicket = $ticket;
                    }
                }
            }
        }
    } catch (Throwable $t) {
        $messages['error'][] = 'Si è verificato un errore inatteso: ' . htmlspecialchars($t->getMessage());
    }
}

if ($selectedTicket === null && $selectedCode) {
    $selectedTicket = TicketService::getTicketForUser($selectedCode, (int)$user['id']);
    if (!$selectedTicket) {
        $messages['error'][] = 'Ticket non trovato o non associato al tuo profilo.';
    }
}

$tickets = [];
try {
    $tickets = TicketService::getTicketsForUser((int)$user['id']);
} catch (Throwable $t) {
    $messages['error'][] = 'Impossibile recuperare i ticket dal database: ' . htmlspecialchars($t->getMessage());
}

include __DIR__ . '/templates/header.php';
?>
<section class="rounded-3xl bg-black/40 border border-white/10 shadow-2xl shadow-indigo-500/20 p-6 md:p-10 space-y-8">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div>
      <h1 class="text-3xl font-bold">Supporto RaceVerse</h1>
      <p class="text-white/70 max-w-2xl">Apri ticket prioritari, monitora lo stato delle richieste e ricevi risposte dal nostro team entro 24 ore lavorative.</p>
    </div>
    <div class="px-4 py-2 rounded-xl bg-white/10 border border-white/10 text-xs uppercase tracking-[0.2em] text-white/70">Codice cliente: <?= htmlspecialchars($user['email']) ?></div>
  </div>

  <?php if (!empty($messages['success']) || !empty($messages['error'])): ?>
    <div class="space-y-3">
      <?php foreach ($messages['success'] as $msg): ?>
        <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-100 text-sm"><?= $msg ?></div>
      <?php endforeach; ?>
      <?php foreach ($messages['error'] as $msg): ?>
        <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-400/40 text-rose-100 text-sm"><?= $msg ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="flex flex-wrap gap-3 text-sm">
    <a href="<?= asset('support.php?tab=create') ?>" class="px-4 py-2 rounded-xl border <?= $activeTab === 'create' ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/70 hover:text-white' ?>">Apri nuovo ticket</a>
    <a href="<?= asset('support.php?tab=timeline') ?>" class="px-4 py-2 rounded-xl border <?= $activeTab === 'timeline' ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/70 hover:text-white' ?>">Storico ticket</a>
  </div>

  <?php if ($activeTab === 'create'): ?>
    <form method="post" class="grid md:grid-cols-2 gap-6 bg-white/5 border border-white/10 rounded-2xl p-6">
      <input type="hidden" name="action" value="open_ticket">
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/80 mb-2" for="support-email">Email di contatto</label>
        <input id="support-email" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required class="w-full px-4 py-3 rounded-xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/80 mb-2" for="support-subject">Oggetto</label>
        <input id="support-subject" type="text" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" placeholder="Es. Assetto personalizzato LMGT3" required class="w-full px-4 py-3 rounded-xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/80 mb-2" for="support-message">Descrizione</label>
        <textarea id="support-message" name="message" rows="6" placeholder="Raccontaci come possiamo aiutarti" required class="w-full px-4 py-3 rounded-2xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
      </div>
      <div class="md:col-span-2 flex flex-wrap gap-3 items-center">
        <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Invia ticket</button>
        <p class="text-xs text-white/50">Riceverai subito il codice ticket da conservare. Potrai aggiornare la richiesta dopo la risposta di un admin.</p>
      </div>
    </form>
  <?php else: ?>
    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1 space-y-4">
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
          <h2 class="text-sm font-semibold text-white/70 uppercase tracking-widest mb-3">I tuoi ticket</h2>
          <div class="space-y-3 max-h-[420px] overflow-auto pr-1">
            <?php if (empty($tickets)): ?>
              <p class="text-white/60 text-sm">Nessun ticket aperto al momento. Apri una richiesta dal tab dedicato.</p>
            <?php else: ?>
              <?php foreach ($tickets as $ticket): ?>
                <?php
                  $isActive = $selectedTicket && $selectedTicket['id'] === $ticket['id'];
                  $badge = $ticket['status'] === 'closed' ? ['bg' => 'bg-rose-500/20 text-rose-200 border-rose-400/30', 'label' => 'Chiuso'] : ($ticket['status'] === 'waiting_user' ? ['bg' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30', 'label' => 'Rispondi'] : ['bg' => 'bg-indigo-500/20 text-indigo-200 border-indigo-400/30', 'label' => 'In lavorazione']);
                ?>
                <a href="<?= asset('support.php?tab=timeline&code=' . urlencode($ticket['code'])) ?>" class="block p-4 rounded-xl border <?= $isActive ? 'border-indigo-400 bg-indigo-500/10 shadow-lg shadow-indigo-500/20' : 'border-white/10 bg-black/30 hover:border-indigo-400/80' ?>">
                  <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="font-semibold text-white/90 line-clamp-2"><?= htmlspecialchars($ticket['subject']) ?></span>
                    <span class="px-2 py-1 rounded-full text-[0.65rem] uppercase tracking-widest border <?= $badge['bg'] ?>"><?= $badge['label'] ?></span>
                  </div>
                  <div class="text-xs text-white/50">Codice: <?= htmlspecialchars($ticket['code']) ?></div>
                  <div class="text-[0.7rem] text-white/40 mt-1">Aggiornato il <?= htmlspecialchars(date('d/m/Y H:i', strtotime($ticket['updated_at']))) ?></div>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="lg:col-span-2">
        <?php if ($selectedTicket): ?>
          <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <h2 class="text-2xl font-semibold text-white"><?= htmlspecialchars($selectedTicket['subject']) ?></h2>
                <p class="text-sm text-white/60">Ticket #<?= htmlspecialchars($selectedTicket['code']) ?> • Stato: <span class="font-semibold text-white/80"><?= htmlspecialchars($selectedTicket['status']) ?></span></p>
              </div>
              <div class="text-xs text-white/50 text-right">
                Aperto il <?= htmlspecialchars(date('d/m/Y H:i', strtotime($selectedTicket['created_at']))) ?><br>
                Ultimo update <?= htmlspecialchars(date('d/m/Y H:i', strtotime($selectedTicket['updated_at']))) ?>
              </div>
            </div>
            <div class="space-y-4">
              <?php foreach (($selectedTicket['messages'] ?? []) as $message): ?>
                <?php
                  $isAdmin = $message['sender_type'] === 'admin';
                  $bubbleClasses = $isAdmin ? 'bg-gradient-to-r from-indigo-500/80 via-purple-500/80 to-emerald-500/80 text-black' : 'bg-black/60 border border-white/10 text-white';
                  $align = $isAdmin ? 'items-end text-right' : 'items-start text-left';
                ?>
                <div class="flex flex-col <?= $align ?>">
                  <div class="text-xs text-white/50 mb-1">
                    <?= $isAdmin ? 'RaceVerse Support' : 'Tu' ?> • <?= htmlspecialchars(date('d/m/Y H:i', strtotime($message['created_at']))) ?>
                  </div>
                  <div class="px-4 py-3 rounded-2xl <?= $bubbleClasses ?> max-w-xl whitespace-pre-line leading-relaxed">
                    <?= nl2br(htmlspecialchars($message['body'])) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if ($selectedTicket['status'] === 'closed'): ?>
              <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-400/30 text-emerald-100 text-sm">
                Ticket chiuso. Per nuove richieste apri un altro ticket dal tab dedicato.
              </div>
            <?php elseif (TicketService::userCanReply($selectedTicket)): ?>
              <form method="post" class="space-y-3">
                <input type="hidden" name="action" value="user_reply">
                <input type="hidden" name="code" value="<?= htmlspecialchars($selectedTicket['code']) ?>">
                <label class="block text-sm font-semibold text-white/80" for="reply-box">Rispondi al team</label>
                <textarea id="reply-box" name="reply" rows="4" class="w-full px-4 py-3 rounded-2xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400" placeholder="Scrivi la tua risposta..."></textarea>
                <div class="flex flex-wrap gap-3 items-center">
                  <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/20">Invia risposta</button>
                  <p class="text-xs text-white/50">Puoi inviare al massimo due aggiornamenti dopo la replica dell'admin.</p>
                </div>
              </form>
            <?php else: ?>
              <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-sm text-white/70">
                Attendi la risposta del team o l'apertura di un nuovo ticket per ulteriori aggiornamenti.
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-white/70 text-sm">
            Seleziona un ticket per visualizzare la conversazione completa e rispondere al supporto.
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
