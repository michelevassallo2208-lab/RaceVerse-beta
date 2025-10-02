<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/TicketService.php';
require_once __DIR__ . '/../src/Database.php';

Auth::start();
$admin = Auth::user();
if (!$admin || ($admin['role'] ?? '') !== 'admin') {
    redirect_to('login.php');
}

$messages = ['success' => [], 'error' => []];
$allowedStatuses = ['waiting_admin', 'waiting_user', 'closed'];
$statusFilter = $_GET['status'] ?? '';
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}
$selectedCode = $_GET['code'] ?? '';
$selectedTicket = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'admin_reply') {
            $code = $_POST['code'] ?? '';
            $message = trim($_POST['message'] ?? '');
            $close = !empty($_POST['close_ticket']);
            if ($code === '') {
                $messages['error'][] = 'Ticket non valido.';
            } else {
                $ticket = TicketService::getTicketByCode($code);
                if (!$ticket) {
                    $messages['error'][] = 'Ticket non trovato.';
                } elseif ($ticket['status'] === 'closed') {
                    $messages['error'][] = 'Il ticket è già chiuso.';
                } elseif ($message === '') {
                    $messages['error'][] = 'Scrivi un messaggio prima di inviare la risposta.';
                } else {
                    $updated = TicketService::addAdminReply($ticket, (int)$admin['id'], $message, $close);
                    if ($updated) {
                        $messages['success'][] = $close ? 'Risposta inviata e ticket chiuso con successo.' : 'Risposta inviata al cliente.';
                        $selectedTicket = $updated;
                        $selectedCode = $updated['code'];
                    } else {
                        $messages['error'][] = 'Impossibile aggiornare il ticket.';
                    }
                }
            }
        } elseif ($action === 'close_ticket') {
            $code = $_POST['code'] ?? '';
            if ($code === '') {
                $messages['error'][] = 'Ticket non valido.';
            } else {
                $ticket = TicketService::getTicketByCode($code);
                if (!$ticket) {
                    $messages['error'][] = 'Ticket non trovato.';
                } elseif ($ticket['status'] === 'closed') {
                    $messages['error'][] = 'Il ticket è già chiuso.';
                } else {
                    TicketService::closeTicket((int)$ticket['id']);
                    $messages['success'][] = 'Ticket chiuso senza inviare nuove risposte.';
                    $selectedTicket = TicketService::getTicketByCode($code);
                    $selectedCode = $code;
                }
            }
        }
    } catch (Throwable $t) {
        $messages['error'][] = 'Errore imprevisto: ' . htmlspecialchars($t->getMessage());
    }
}

if ($selectedTicket === null && $selectedCode !== '') {
    $selectedTicket = TicketService::getTicketByCode($selectedCode);
    if (!$selectedTicket) {
        $messages['error'][] = 'Ticket selezionato non trovato.';
    }
}

try {
    $tickets = TicketService::getTicketsForAdmin($statusFilter !== '' ? $statusFilter : null);
} catch (Throwable $t) {
    $tickets = [];
    $messages['error'][] = 'Impossibile caricare i ticket: ' . htmlspecialchars($t->getMessage());
}

function ticket_status_badge(string $status): array {
    return match ($status) {
        'waiting_admin' => ['label' => 'In attesa admin', 'classes' => 'bg-indigo-500/20 text-indigo-200 border-indigo-400/30'],
        'waiting_user' => ['label' => 'In attesa cliente', 'classes' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30'],
        'closed' => ['label' => 'Chiuso', 'classes' => 'bg-rose-500/20 text-rose-200 border-rose-400/30'],
        default => ['label' => $status, 'classes' => 'bg-white/10 text-white/70 border-white/20'],
    };
}

function ticket_user_meta(?array $ticket): ?array {
    if (!$ticket || empty($ticket['user_id'])) {
        return null;
    }
    try {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT email, subscription_plan, subscription_active FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$ticket['user_id']]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Throwable $t) {
        return null;
    }
}

$userMeta = ticket_user_meta($selectedTicket);
?><!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ticket • RaceVerse Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= asset('../assets/css/style.css') ?>">
</head>
<body class="bg-[#05060b] text-white premium-texture min-h-screen">
  <div class="max-w-6xl mx-auto px-4 py-10 space-y-8">
    <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div class="flex items-center gap-4">
        <span class="inline-flex items-center justify-center w-14 h-14 rounded-3xl bg-gradient-to-br from-indigo-500 via-purple-500 to-emerald-500 shadow-lg shadow-indigo-500/40">
          <img src="<?= asset('../assets/images/logo.png') ?>" class="w-9 h-9" alt="RaceVerse logo">
        </span>
        <div>
          <h1 class="text-3xl font-bold">Ticket di assistenza</h1>
          <p class="text-white/60">Gestisci le richieste utenti, rispondi e chiudi i ticket quando risolti.</p>
        </div>
      </div>
      <div class="flex flex-wrap gap-3">
        <a href="<?= asset('index.php') ?>" class="px-5 py-3 rounded-2xl bg-white/10 border border-white/10 text-white/80 hover:text-white">← Dashboard admin</a>
        <a href="<?= asset('../logout.php') ?>" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-rose-500 via-fuchsia-500 to-purple-500 text-black font-semibold shadow-lg shadow-rose-500/30">Logout</a>
      </div>
    </header>

    <?php foreach ($messages['success'] as $message): ?>
      <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-200 text-sm flex items-center gap-3">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span><?= $message ?></span>
      </div>
    <?php endforeach; ?>

    <?php foreach ($messages['error'] as $message): ?>
      <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-400/40 text-rose-200 text-sm flex items-center gap-3">
        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
        <span><?= $message ?></span>
      </div>
    <?php endforeach; ?>

    <section class="rounded-3xl bg-black/60 border border-white/10 shadow-2xl shadow-indigo-500/20 p-6 space-y-6">
      <div class="flex flex-wrap gap-3 text-sm">
        <a href="<?= asset('tickets.php') ?>" class="px-4 py-2 rounded-xl border <?= $statusFilter === '' ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/70 hover:text-white' ?>">Tutti</a>
        <a href="<?= asset('tickets.php?status=waiting_admin') ?>" class="px-4 py-2 rounded-xl border <?= $statusFilter === 'waiting_admin' ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/70 hover:text-white' ?>">In attesa admin</a>
        <a href="<?= asset('tickets.php?status=waiting_user') ?>" class="px-4 py-2 rounded-xl border <?= $statusFilter === 'waiting_user' ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/70 hover:text-white' ?>">In attesa cliente</a>
        <a href="<?= asset('tickets.php?status=closed') ?>" class="px-4 py-2 rounded-xl border <?= $statusFilter === 'closed' ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/70 hover:text-white' ?>">Chiusi</a>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
          <div class="rounded-2xl bg-white/5 border border-white/10 max-h-[480px] overflow-auto">
            <?php if (empty($tickets)): ?>
              <p class="p-6 text-sm text-white/60">Nessun ticket nella categoria selezionata.</p>
            <?php else: ?>
              <ul class="divide-y divide-white/5">
                <?php foreach ($tickets as $ticket): ?>
                  <?php $badge = ticket_status_badge($ticket['status']); ?>
                  <li>
                    <a href="<?= asset('tickets.php?' . http_build_query(array_filter(['status' => $statusFilter ?: null, 'code' => $ticket['code']]))) ?>" class="block p-4 hover:bg-indigo-500/10 <?= ($selectedTicket && $selectedTicket['id'] === $ticket['id']) ? 'bg-indigo-500/10 border-l-2 border-indigo-400' : '' ?>">
                      <div class="flex items-center justify-between gap-3">
                        <span class="font-semibold text-white/90 line-clamp-2"><?= htmlspecialchars($ticket['subject']) ?></span>
                        <span class="px-2 py-1 rounded-full text-[0.65rem] uppercase tracking-widest border <?= $badge['classes'] ?>"><?= $badge['label'] ?></span>
                      </div>
                      <div class="text-xs text-white/50 mt-2 flex flex-col gap-1">
                        <span>Codice: <?= htmlspecialchars($ticket['code']) ?></span>
                        <span><?= htmlspecialchars($ticket['email']) ?></span>
                        <span>Aggiornato: <?= htmlspecialchars(date('d/m H:i', strtotime($ticket['updated_at']))) ?></span>
                      </div>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>
        <div class="lg:col-span-2">
          <?php if ($selectedTicket): ?>
            <?php $badge = ticket_status_badge($selectedTicket['status']); ?>
            <div class="rounded-2xl bg-white/5 border border-white/10 p-6 space-y-6">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                  <h2 class="text-2xl font-semibold text-white"><?= htmlspecialchars($selectedTicket['subject']) ?></h2>
                  <div class="flex flex-wrap gap-2 text-xs text-white/60 mt-2">
                    <span class="px-2 py-1 rounded-full border <?= $badge['classes'] ?>"><?= $badge['label'] ?></span>
                    <span>Codice: <?= htmlspecialchars($selectedTicket['code']) ?></span>
                    <span>Follow-up utente: <?= (int)$selectedTicket['user_followups'] ?>/2</span>
                  </div>
                </div>
                <div class="text-xs text-white/50 text-right">
                  Aperto il <?= htmlspecialchars(date('d/m/Y H:i', strtotime($selectedTicket['created_at']))) ?><br>
                  Ultimo update <?= htmlspecialchars(date('d/m/Y H:i', strtotime($selectedTicket['updated_at']))) ?>
                </div>
              </div>
              <div class="grid sm:grid-cols-2 gap-4 text-sm text-white/70">
                <div class="p-4 rounded-xl bg-black/40 border border-white/10">
                  <div class="text-xs text-white/50 uppercase tracking-widest mb-1">Email</div>
                  <div class="font-semibold text-white/90"><?= htmlspecialchars($selectedTicket['email']) ?></div>
                </div>
                <div class="p-4 rounded-xl bg-black/40 border border-white/10">
                  <div class="text-xs text-white/50 uppercase tracking-widest mb-1">Account collegato</div>
                  <div class="font-semibold text-white/90">
                    <?php if ($userMeta): ?>
                      <?= htmlspecialchars($userMeta['email']) ?>
                      <span class="block text-xs text-white/50">Piano: <?= htmlspecialchars($userMeta['subscription_plan'] ?: 'BASIC') ?> <?= !empty($userMeta['subscription_active']) ? '• PRO attivo' : '' ?></span>
                    <?php else: ?>
                      Ospite / non registrato
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="space-y-4 max-h-[320px] overflow-auto pr-1">
                <?php foreach (($selectedTicket['messages'] ?? []) as $message): ?>
                  <?php $isAdmin = $message['sender_type'] === 'admin'; ?>
                  <?php $adminLabel = $isAdmin ? ('Admin #' . ($message['sender_id'] ? (int)$message['sender_id'] : 'Team')) : $selectedTicket['email']; ?>
                  <div class="flex flex-col <?= $isAdmin ? 'items-end text-right' : 'items-start text-left' ?>">
                    <div class="text-xs text-white/50 mb-1">
                      <?= $isAdmin ? 'RaceVerse (' . htmlspecialchars($adminLabel) . ')' : htmlspecialchars($adminLabel) ?> • <?= htmlspecialchars(date('d/m/Y H:i', strtotime($message['created_at']))) ?>
                    </div>
                    <div class="px-4 py-3 rounded-2xl <?= $isAdmin ? 'bg-gradient-to-r from-indigo-500/80 via-purple-500/80 to-emerald-500/80 text-black' : 'bg-black/60 border border-white/10 text-white' ?> max-w-xl whitespace-pre-line leading-relaxed">
                      <?= nl2br(htmlspecialchars($message['body'])) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php if ($selectedTicket['status'] === 'closed'): ?>
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-400/30 text-emerald-100 text-sm">Ticket chiuso. Nessuna ulteriore azione disponibile da questa schermata.</div>
              <?php else: ?>
                <form method="post" class="space-y-4">
                  <input type="hidden" name="action" value="admin_reply">
                  <input type="hidden" name="code" value="<?= htmlspecialchars($selectedTicket['code']) ?>">
                  <div>
                    <label for="reply-message" class="block text-sm font-semibold text-white/80 mb-2">Risposta al cliente</label>
                    <textarea id="reply-message" name="message" rows="5" class="w-full px-4 py-3 rounded-2xl bg-black/40 border border-white/15 text-white placeholder:text-white/40 focus:outline-none focus:border-indigo-400" placeholder="Scrivi qui la risposta dettagliata"></textarea>
                  </div>
                  <div class="flex flex-wrap items-center gap-4 text-sm">
                    <label class="inline-flex items-center gap-2 text-white/70">
                      <input type="checkbox" name="close_ticket" value="1" class="w-4 h-4 rounded border-white/20 bg-black/40">
                      Chiudi ticket dopo l'invio
                    </label>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 text-black font-semibold shadow-lg shadow-emerald-500/20">Invia risposta</button>
                  </div>
                </form>
                <form method="post" class="pt-2">
                  <input type="hidden" name="action" value="close_ticket">
                  <input type="hidden" name="code" value="<?= htmlspecialchars($selectedTicket['code']) ?>">
                  <button type="submit" class="text-xs text-white/50 hover:text-white underline">Chiudi ticket senza risposta</button>
                </form>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="rounded-2xl bg-white/5 border border-white/10 p-6 text-sm text-white/70">Seleziona un ticket per visualizzare i dettagli e rispondere.</div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</body>
</html>
