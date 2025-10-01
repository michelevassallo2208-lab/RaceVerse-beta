<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class Mailer
{
    protected static function config(): array
    {
        static $config;
        if ($config === null) {
            $config = require __DIR__ . '/config.php';
        }
        return $config['mail'] ?? [];
    }

    public static function sendVerificationEmail(string $toEmail, string $toName, string $verifyUrl): bool
    {
        $mailConfig = self::config();
        if (empty($mailConfig['host']) || empty($mailConfig['username']) || empty($mailConfig['password'])) {
            throw new RuntimeException('Configurazione SMTP mancante.');
        }

        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException('Libreria PHPMailer non disponibile.');
        }

        try {
            $mailer = new PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $mailConfig['host'];
            $mailer->Port = (int)($mailConfig['port'] ?? 465);
            $mailer->SMTPAuth = true;
            $encryption = strtolower($mailConfig['encryption'] ?? 'ssl');
            if ($encryption === 'tls') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            $mailer->Username = $mailConfig['username'];
            $mailer->Password = $mailConfig['password'];
            $mailer->setFrom($mailConfig['from_email'] ?? $mailConfig['username'], $mailConfig['from_name'] ?? 'RaceVerse');
            $mailer->addAddress($toEmail, $toName ?: $toEmail);
            $mailer->CharSet = 'UTF-8';
            $mailer->isHTML(true);

            $mailer->Subject = 'Conferma la tua iscrizione a RaceVerse PRO';

            $htmlBody = <<<HTML
                <div style="font-family: 'Segoe UI', Tahoma, sans-serif; background:#05060d; color:#f7f8ff; padding:32px;">
                  <div style="max-width:520px;margin:0 auto;background:linear-gradient(135deg,#101225,#1b2340);border-radius:24px;border:1px solid rgba(147,197,253,0.25);overflow:hidden;">
                    <div style="padding:28px 32px;">
                      <h1 style="font-size:24px;margin:0 0 12px;color:#8ef6c0;">Benvenuto in RaceVerse</h1>
                      <p style="line-height:1.6;margin:0 0 18px;">Ciao {$toName},<br>grazie per esserti registrato a <strong>RaceVerse PRO</strong>. Conferma l'email per attivare la tua membership premium da €2,99/mese.</p>
                      <p style="margin:0 0 22px;">
                        <a href="{$verifyUrl}" style="display:inline-block;padding:14px 20px;border-radius:999px;background:linear-gradient(135deg,#34f5c5,#4b9fff);color:#05060d;text-decoration:none;font-weight:600;">Attiva il mio account</a>
                      </p>
                      <p style="font-size:13px;line-height:1.6;margin:0;color:#cbd5f5;">Se il pulsante non funziona copia e incolla questo link nel browser:<br><span style="word-break:break-all;color:#8ef6c0;">{$verifyUrl}</span></p>
                    </div>
                    <div style="background:rgba(20,25,45,0.85);padding:20px 32px;font-size:12px;color:#7d89b6;">
                      Ricevi questa email perché hai richiesto l'attivazione di RaceVerse PRO. Per supporto scrivi a <a href="mailto:support@raceverse.it" style="color:#8ef6c0;">support@raceverse.it</a>.
                    </div>
                  </div>
                </div>
            HTML;

            $mailer->Body = $htmlBody;
            $mailer->AltBody = "Ciao {$toName},\nConferma la tua iscrizione a RaceVerse PRO visitando questo link: {$verifyUrl}";

            return $mailer->send();
        } catch (MailException $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            error_log('Mailer throwable: ' . $e->getMessage());
            return false;
        }
    }
}
