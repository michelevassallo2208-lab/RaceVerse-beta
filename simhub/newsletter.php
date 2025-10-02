<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Mailer.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

$rawEmail = trim($_POST['email'] ?? '');
$email = filter_var($rawEmail, FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Inserisci un indirizzo email valido.']);
    exit;
}

try {
    $pdo = Database::pdo();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id, unsubscribe_token FROM newsletter_subscriptions WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    $alreadySubscribed = (bool)$existing;

    if ($alreadySubscribed) {
        $token = $existing['unsubscribe_token'] ?? '';
        if ($token === '' || $token === null) {
            $token = bin2hex(random_bytes(16));
            $update = $pdo->prepare('UPDATE newsletter_subscriptions SET unsubscribe_token = ? WHERE id = ?');
            $update->execute([$token, $existing['id']]);
        }
    } else {
        $token = bin2hex(random_bytes(16));
        $insert = $pdo->prepare('INSERT INTO newsletter_subscriptions (email, unsubscribe_token) VALUES (?, ?)');
        $insert->execute([$email, $token]);
    }

    $pdo->commit();

    $unsubscribeUrl = Mailer::absoluteUrl('newsletter-unsubscribe.php?token=' . urlencode($token));

    if (!$alreadySubscribed) {
        try {
            Mailer::sendNewsletterWelcome($email, $unsubscribeUrl);
        } catch (Throwable $mailError) {
            error_log('Newsletter welcome email error: ' . $mailError->getMessage());
        }
    }

    echo json_encode([
        'success' => true,
        'message' => $alreadySubscribed
            ? 'Sei già iscritto alla newsletter RaceVerse. Puoi gestire le preferenze dall’ultima email ricevuta.'
            : 'Iscrizione completata! Controlla la tua email per il bonus di benvenuto RaceVerse.'
    ]);
    exit;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Newsletter subscribe error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossibile completare l\'iscrizione ora. Riprova tra pochi minuti.']);
    exit;
}
