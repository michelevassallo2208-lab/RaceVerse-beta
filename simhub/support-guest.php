<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/TicketService.php';

Auth::start();
if (Auth::user()) {
    redirect_to('support.php');
}

$messages = ['success' => [], 'error' => []];
$activeTab = $_GET['tab'] ?? 'create';
$lookupTicket = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'open_ticket') {
            $email = trim($_POST['email'] ?? '');
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
                $ticket = TicketService::createTicket(null, $email, $subject, $body);
                if ($ticket) {
                    $messages['success'][] = 'Ticket aperto! Conserva il codice <strong>' . htmlspecialchars($ticket['code']) . '</strong> e la tua email per seguire la conversazione.';
                    $activeTab = 'lookup';
                    $lookupTicket = $ticket;
                } else {
                    $messages['error'][] = 'Non è stato possibile aprire il ticket al momento. Riprova più tardi.';
                }
            }
        } elseif ($action === 'lookup_ticket') {
            $email = trim($_POST['email'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $activeTab = 'lookup';
            if ($email === '' || $code === '') {
                $messages['error'][] = 'Inserisci codice ticket ed email utilizzata.';
            } else {
                $ticket = TicketService::getTicketByCodeAndEmail($code, $email);
                if ($ticket) {
                    $lookupTicket = $ticket;
                } else {
                    $messages['error'][] = 'Ticket non trovato. Controlla codice e indirizzo email.';
                }
            }
        } elseif ($action === 'guest_reply') {
            $email = trim($_POST['email'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $reply = trim($_POST['reply'] ?? '');
            $activeTab = 'lookup';
            if ($email === '' || $code === '') {
                $messages['error'][] = 'Ticket non valido. Reinserisci codice ed email.';
            } else {
                $ticket = TicketService::getTicketByCodeAndEmail($code, $email);
                if (!$ticket) {
                    $messages['error'][] = 'Ticket non trovato. Controlla le informazioni inserite.';
                } elseif (!TicketService::userCanReply($ticket)) {
                    $messages['error'][] = 'Hai già inviato il massimo aggiornamento consentito oppure il ticket è chiuso.';
                    $lookupTicket = $ticket;
                } elseif ($reply === '') {
                    $messages['error'][] = 'Scrivi un messaggio per rispondere al supporto.';
                    $lookupTicket = $ticket;
                } else {
                    $updated = TicketService::addUserReply($ticket, null, $reply);
                    if ($updated) {
                        $messages['success'][] = 'Aggiornamento inviato. Ti contatteremo sulla mail indicata.';
                        $lookupTicket = $updated;
                    } else {
                        $messages['error'][] = 'Non è stato possibile registrare l\'aggiornamento.';
                        $lookupTicket = $ticket;
                    }
                }
            }
        }
    } catch (Throwable $t) {
        $messages['error'][] = 'Errore imprevisto: ' . htmlspecialchars($t->getMessage());
    }
}

include __DIR__ . '/templates/header.php';
?>
<section class="rounded-3xl bg-black/40 border border-white/10 shadow-2xl shadow-emerald-500/20 p-6 md:p-10 space-y-8">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div>
      <h1 class="text-3xl font-bold">Supporto RaceVerse</h1>
      <p class="text-white/70 max-w-2xl">Non hai ancora un account? Nessun problema: apri un ticket come ospite e conserva il codice per seguire lo stato della richiesta.</p>
    </div>
    <div class="text-xs text-white/60 bg-white/5 border border-white/10 px-4 py-2 rounded-xl">Consigliato: crea un account BASIC gratuito per risposte più rapide.</div>
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
    <a href="<?= asset('support-guest.php?tab=create') ?>" class="px-4 py-2 rounded-xl border <?= $activeTab === 'create' ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/70 hover:text-white' ?>">Apri nuovo ticket</a>
    <a href="<?= asset('support-guest.php?tab=lookup') ?>" class="px-4 py-2 rounded-xl border <?= $activeTab === 'lookup' ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/70 hover:text-white' ?>">Verifica ticket</a>
  </div>

  <?php if ($activeTab === 'create'): ?>
    <form method="post" class="grid md:grid-cols-2 gap-6 bg-white/5 border border-white/10 rounded-2xl p-6">
      <input type="hidden" name="action" value="open_ticket">
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/80 mb-2" for="guest-email">Email</label>
        <input id="guest-email" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400" placeholder="you@example.com">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/80 mb-2" for="guest-subject">Oggetto</label>
        <input id="guest-subject" type="text" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400" placeholder="Es. Dubbi su RaceVerse PRO">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-white/80 mb-2" for="guest-message">Descrizione</label>
        <textarea id="guest-message" name="message" rows="6" required class="w-full px-4 py-3 rounded-2xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400" placeholder="Raccontaci di cosa hai bisogno"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
      </div>
      <div class="md:col-span-2 flex flex-wrap gap-3 items-center">
        <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/30">Invia ticket</button>
        <p class="text-xs text-white/50">Riceverai aggiornamenti esclusivamente via email: controlla lo spam se non trovi le nostre risposte.</p>
      </div>
    </form>
  <?php else: ?>
    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1">
        <form method="post" class="space-y-4 p-6 rounded-2xl bg-white/5 border border-white/10">
          <input type="hidden" name="action" value="lookup_ticket">
          <div>
            <label class="block text-sm font-semibold text-white/80 mb-2" for="lookup-code">Codice ticket</label>
            <input id="lookup-code" type="text" name="code" value="<?= htmlspecialchars($_POST['code'] ?? ($lookupTicket['code'] ?? '')) ?>" required class="w-full px-4 py-3 rounded-xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400" placeholder="RV-XXXXXX">
          </div>
          <div>
            <label class="block text-sm font-semibold text-white/80 mb-2" for="lookup-email">Email utilizzata</label>
            <input id="lookup-email" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? ($lookupTicket['email'] ?? '')) ?>" required class="w-full px-4 py-3 rounded-xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400" placeholder="you@example.com">
          </div>
          <button type="submit" class="w-full px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/30">Vedi conversazione</button>
          <p class="text-xs text-white/50">Hai smarrito il codice? Contatta support@raceverse.it indicando l'indirizzo email utilizzato.</p>
        </form>
      </div>
      <div class="lg:col-span-2">
        <?php if ($lookupTicket): ?>
          <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <h2 class="text-2xl font-semibold text-white"><?= htmlspecialchars($lookupTicket['subject']) ?></h2>
                <p class="text-sm text-white/60">Ticket #<?= htmlspecialchars($lookupTicket['code']) ?> • Stato: <span class="font-semibold text-white/80"><?= htmlspecialchars($lookupTicket['status']) ?></span></p>
              </div>
              <div class="text-xs text-white/50 text-right">
                Aperto il <?= htmlspecialchars(date('d/m/Y H:i', strtotime($lookupTicket['created_at']))) ?><br>
                Ultimo update <?= htmlspecialchars(date('d/m/Y H:i', strtotime($lookupTicket['updated_at']))) ?>
              </div>
            </div>
            <div class="space-y-4">
              <?php foreach (($lookupTicket['messages'] ?? []) as $message): ?>
                <?php $isAdmin = $message['sender_type'] === 'admin'; ?>
                <div class="flex flex-col <?= $isAdmin ? 'items-end text-right' : 'items-start text-left' ?>">
                  <div class="text-xs text-white/50 mb-1">
                    <?= $isAdmin ? 'RaceVerse Support' : htmlspecialchars($lookupTicket['email']) ?> • <?= htmlspecialchars(date('d/m/Y H:i', strtotime($message['created_at']))) ?>
                  </div>
                  <div class="px-4 py-3 rounded-2xl <?= $isAdmin ? 'bg-gradient-to-r from-indigo-500/80 via-purple-500/80 to-emerald-500/80 text-black' : 'bg-black/60 border border-white/10 text-white' ?> max-w-xl whitespace-pre-line leading-relaxed">
                    <?= nl2br(htmlspecialchars($message['body'])) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if ($lookupTicket['status'] === 'closed'): ?>
              <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-400/30 text-emerald-100 text-sm">Ticket chiuso. Per ulteriori informazioni apri una nuova richiesta.</div>
            <?php elseif (TicketService::userCanReply($lookupTicket)): ?>
              <form method="post" class="space-y-3">
                <input type="hidden" name="action" value="guest_reply">
                <input type="hidden" name="code" value="<?= htmlspecialchars($lookupTicket['code']) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($lookupTicket['email']) ?>">
                <label class="block text-sm font-semibold text-white/80" for="guest-reply">Rispondi al team</label>
                <textarea id="guest-reply" name="reply" rows="4" class="w-full px-4 py-3 rounded-2xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400" placeholder="Scrivi il tuo aggiornamento"></textarea>
                <div class="flex flex-wrap gap-3 items-center">
                  <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/20">Invia aggiornamento</button>
                  <p class="text-xs text-white/50">Massimo due aggiornamenti dopo la risposta di un admin.</p>
                </div>
              </form>
            <?php else: ?>
              <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-sm text-white/70">Attendi una risposta dal team oppure apri un nuovo ticket.</div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-white/70 text-sm">Inserisci codice ed email per consultare la conversazione con RaceVerse.</div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
