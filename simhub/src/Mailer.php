<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    protected static function makeMailer(): PHPMailer
    {
        $config = require __DIR__ . '/config.php';

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['mail']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['mail']['username'];
        $mail->Password = $config['mail']['password'];

        $encryption = strtolower($config['mail']['encryption'] ?? '');
        if ($encryption === 'ssl' || $encryption === 'tls') {
            $mail->SMTPSecure = $encryption === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->Port = (int)($config['mail']['port'] ?? 25);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);

        return $mail;
    }

    public static function sendVerificationEmail(string $email, string $firstName, string $lastName, string $token): void
    {
        $config = require __DIR__ . '/config.php';
        $displayName = trim($firstName . ' ' . $lastName);
        $baseUrl = rtrim($config['app_url'] ?? '', '/');
        $confirmUrl = $baseUrl . '/confirm.php?token=' . urlencode($token) . '&email=' . urlencode($email);
        $logoUrl = $baseUrl . '/assets/images/logo.png';

        $mail = self::makeMailer();
        $mail->addAddress($email, $displayName ?: $email);
        $mail->isHTML(true);
        $mail->Subject = 'Benvenuto in Raceverse - Conferma la tua registrazione';

        $mail->Body = self::renderWelcomeTemplate($displayName ?: $email, $confirmUrl, $logoUrl);
        $mail->AltBody = self::renderWelcomeText($displayName ?: $email, $confirmUrl);

        $mail->send();
    }

    protected static function renderWelcomeTemplate(string $name, string $confirmUrl, string $logoUrl): string
    {
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escapedUrl = htmlspecialchars($confirmUrl, ENT_QUOTES, 'UTF-8');
        $escapedLogo = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <title>Benvenuto in Raceverse</title>
    <style>
      body { background-color: #0f1117; color: #f1f5f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
      .container { max-width: 560px; margin: 0 auto; padding: 32px 24px; }
      .card { background: linear-gradient(135deg, rgba(79,70,229,0.15), rgba(236,72,153,0.12)); border-radius: 24px; padding: 32px; border: 1px solid rgba(255,255,255,0.15); }
      .logo { width: 72px; height: 72px; margin-bottom: 16px; }
      .btn { display: inline-block; padding: 14px 28px; border-radius: 9999px; background: #f8fafc; color: #0f1117; font-weight: 600; text-decoration: none; }
      .footer { margin-top: 32px; font-size: 12px; color: rgba(241,245,249,0.7); text-align: center; }
      p { line-height: 1.5; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="card">
        <img src="{$escapedLogo}" alt="Raceverse" class="logo">
        <h1 style="margin-top: 0; font-size: 26px;">Benvenuto a bordo, {$escapedName}!</h1>
        <p>Siamo felici che tu abbia scelto Raceverse per vivere l'esperienza delle corse virtuali con dati e assetti sempre aggiornati.</p>
        <p>Per completare l'iscrizione e attivare il tuo profilo ti basta confermare l'indirizzo email.</p>
        <p style="text-align: center; margin: 28px 0;">
          <a href="{$escapedUrl}" class="btn">Conferma la tua registrazione</a>
        </p>
        <p>Se il pulsante non dovesse funzionare, copia e incolla il seguente link nel tuo browser:</p>
        <p style="word-break: break-all;">{$escapedUrl}</p>
        <p>A presto in pista!<br>Il team Raceverse</p>
      </div>
      <div class="footer">
        Ricevi questa email perché hai richiesto un account su Raceverse. Se non hai effettuato tu la richiesta, ignora questo messaggio.
      </div>
    </div>
  </body>
</html>
HTML;
    }

    protected static function renderWelcomeText(string $name, string $confirmUrl): string
    {
        return "Ciao {$name},\n\nBenvenuto in Raceverse! Conferma il tuo indirizzo email per completare la registrazione: {$confirmUrl}\n\nSe non hai effettuato la richiesta puoi ignorare questo messaggio.\n\nA presto in pista!\nIl team Raceverse";
    }
}
