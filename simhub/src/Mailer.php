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

    protected static function bootMailer(): PHPMailer
    {
        $mailConfig = self::config();
        if (empty($mailConfig['host']) || empty($mailConfig['username']) || empty($mailConfig['password'])) {
            throw new RuntimeException('Configurazione SMTP mancante.');
        }

        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException('Libreria PHPMailer non disponibile.');
        }

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
        $mailer->CharSet = 'UTF-8';
        $mailer->isHTML(true);

        return $mailer;
    }

    public static function sendVerificationEmail(string $toEmail, string $toName, string $verifyUrl): bool
    {
        try {
            $mailer = self::bootMailer();
            $safeName = htmlspecialchars($toName ?: 'pilota', ENT_QUOTES, 'UTF-8');
            $safeUrl = htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8');

            $mailer->addAddress($toEmail, $toName ?: $toEmail);
            $mailer->Subject = 'Benvenuto su RaceVerse – attiva il tuo profilo';

            $htmlBody = <<<HTML
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#040511;padding:40px 0;font-family:'Poppins',Arial,sans-serif;">
                  <tr>
                    <td align="center">
                      <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560" style="max-width:90%;background:linear-gradient(145deg,#0c1230,#1e2a5c,#0b182b);border-radius:28px;overflow:hidden;border:1px solid rgba(76,110,245,0.35);box-shadow:0 25px 60px rgba(20,40,110,0.35);color:#ecf2ff;">
                        <tr>
                          <td style="padding:36px 44px 18px; text-align:left;">
                            <div style="font-size:13px;text-transform:uppercase;letter-spacing:3px;color:#a9b7ff;">RaceVerse BASIC</div>
                            <h1 style="font-size:30px;line-height:1.3;margin:12px 0;color:#ffffff;">Ciao {$safeName}, illumina la tua prossima gara</h1>
                            <p style="margin:18px 0 24px;line-height:1.7;color:#dce3ff;">Il tuo profilo è quasi pronto. Conferma l'indirizzo email per entrare nella community RaceVerse, accedere ai dati BASIC gratuiti e sbloccare quando vuoi il mondo <strong>RaceVerse PRO a 2,99€</strong>.</p>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:26px;">
                              <tr>
                                <td bgcolor="#38f8c0" style="border-radius:999px;">
                                  <a href="{$safeUrl}" style="display:inline-block;padding:16px 28px;font-weight:600;text-decoration:none;color:#041027;font-size:15px;">Attiva subito il mio account</a>
                                </td>
                              </tr>
                            </table>
                            <p style="font-size:14px;line-height:1.7;color:#afbcff;">Oppure incolla questo link nel tuo browser:<br><span style="color:#58ffe2;word-break:break-all;">{$safeUrl}</span></p>
                          </td>
                        </tr>
                        <tr>
                          <td style="padding:0 44px 34px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:rgba(15,24,56,0.75);border-radius:20px;padding:24px;color:#9bb3ff;">
                              <tr>
                                <td style="font-size:13px;line-height:1.8;">
                                  <strong>Cosa ottieni ora:</strong>
                                  <ul style="margin:12px 0 0;padding-left:18px;">
                                    <li>Accesso immediato a RaceVerse BASIC gratuito</li>
                                    <li>Setup premium e telemetria PRO pronti quando deciderai di passare di livello</li>
                                    <li>Supporto dedicato: <a href="mailto:support@raceverse.it" style="color:#58ffe2;text-decoration:none;">support@raceverse.it</a></li>
                                  </ul>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td style="padding:22px 44px 32px;font-size:12px;color:#7180c7;background:#070b1d;">
                            Ricevi questa comunicazione perché hai richiesto la creazione di un account RaceVerse. Se non fossi stato tu puoi ignorare questo messaggio.
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
            HTML;

            $mailer->Body = $htmlBody;
            $mailer->AltBody = "Ciao {$toName},\nConferma il tuo account RaceVerse visitando questo link: {$verifyUrl}.";

            return $mailer->send();
        } catch (MailException $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            error_log('Mailer throwable: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendNewsletterWelcome(string $toEmail): bool
    {
        try {
            $mailer = self::bootMailer();
            $mailer->addAddress($toEmail, $toEmail);
            $mailer->Subject = 'RaceVerse – Benvenuto nella newsletter ufficiale';

            $safeEmail = htmlspecialchars($toEmail, ENT_QUOTES, 'UTF-8');
            $htmlBody = <<<HTML
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#03060f;padding:38px 0;font-family:'Poppins',Arial,sans-serif;color:#eef3ff;">
                  <tr>
                    <td align="center">
                      <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560" style="max-width:92%;background:linear-gradient(140deg,#101a45,#132964,#081730);border-radius:28px;overflow:hidden;border:1px solid rgba(88,255,226,0.35);box-shadow:0 20px 55px rgba(8,30,66,0.55);">
                        <tr>
                          <td style="padding:40px 44px 0;text-align:left;">
                            <div style="font-size:12px;letter-spacing:4px;text-transform:uppercase;color:#67ffe6;">Newsletter RaceVerse</div>
                            <h1 style="font-size:32px;line-height:1.25;margin:14px 0;color:#ffffff;">Grazie per esserti iscritto!</h1>
                            <p style="margin:18px 0 22px;line-height:1.7;color:#d5e6ff;">Da oggi riceverai solo i migliori consigli per dominare le classifiche, anteprime dei setup RaceVerse PRO e storie dai paddock virtuali.</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="padding:0 44px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:rgba(13,24,52,0.9);border-radius:20px;padding:26px;color:#b7c8ff;">
                              <tr>
                                <td style="font-size:14px;line-height:1.8;">
                                  <strong>Bonus di benvenuto:</strong>
                                  <p style="margin:12px 0;color:#ecf7ff;">Usa il codice <span style="display:inline-block;padding:6px 14px;border-radius:999px;background:rgba(88,255,226,0.12);color:#67ffe6;font-weight:600;">RACEVERSE10</span> sul tuo prossimo upgrade a RaceVerse PRO e ottieni il <strong>10% di sconto sul primo mese</strong>.</p>
                                  <p style="margin:12px 0 0;color:#d5e6ff;">Tieniti pronto: i nostri consigli tecnici, webinar esclusivi e setup in anteprima arriveranno presto nella tua casella <span style="color:#67ffe6;">{$safeEmail}</span>.</p>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td style="padding:0 44px 40px;font-size:12px;line-height:1.6;color:#8092d6;background:#060d1e;">
                            Email inviata da noreply@raceverse.it. Se desideri annullare l'iscrizione potrai farlo con un clic da ogni nostra comunicazione.
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
            HTML;

            $mailer->Body = $htmlBody;
            $mailer->AltBody = "Benvenuto nella newsletter RaceVerse! Usa il codice RACEVERSE10 per avere il 10% di sconto sul primo mese PRO.";

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
