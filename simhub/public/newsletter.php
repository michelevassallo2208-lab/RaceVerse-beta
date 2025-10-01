<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Mailer.php';

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

    $stmt = $pdo->prepare('SELECT id FROM newsletter_subscriptions WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $alreadySubscribed = (bool)$stmt->fetch();

    if (!$alreadySubscribed) {
        $insert = $pdo->prepare('INSERT INTO newsletter_subscriptions (email) VALUES (?)');
        $insert->execute([$email]);
    }

    $pdo->commit();

    if (!$alreadySubscribed) {
        try {
            Mailer::sendNewsletterWelcome($email);
        } catch (Throwable $mailError) {
            error_log('Newsletter welcome email error: ' . $mailError->getMessage());
        }
    }

    echo json_encode([
        'success' => true,
        'message' => $alreadySubscribed
            ? 'Sei già iscritto alla newsletter RaceVerse. Controlla la tua casella per gli ultimi consigli.'
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
