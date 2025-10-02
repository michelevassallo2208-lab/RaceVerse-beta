<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Mailer.php';

class TicketService
{
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function generateCode(): string
    {
        $alphabet = self::CODE_ALPHABET;
        $length = strlen($alphabet) - 1;
        $code = 'RV-';
        for ($i = 0; $i < 6; $i++) {
            $code .= $alphabet[random_int(0, $length)];
        }
        return $code;
    }

    private static function ensureUniqueCode(PDO $pdo): string
    {
        do {
            $code = self::generateCode();
            $stmt = $pdo->prepare('SELECT id FROM support_tickets WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
        } while ($stmt->fetch());
        return $code;
    }

    public static function createTicket(?int $userId, string $email, string $subject, string $message): ?array
    {
        $email = trim($email);
        $subject = trim($subject);
        $message = trim($message);
        if ($email === '' || $subject === '' || $message === '') {
            return null;
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $code = self::ensureUniqueCode($pdo);
            $stmt = $pdo->prepare('INSERT INTO support_tickets (code, user_id, email, subject, status, user_followups, last_message_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, "waiting_admin", 0, "user", NOW(), NOW())');
            $stmt->execute([$code, $userId, $email, $subject]);
            $ticketId = (int)$pdo->lastInsertId();

            self::insertMessage($pdo, $ticketId, 'user', $userId, $message);

            $pdo->commit();
            return self::getTicketById($ticketId);
        } catch (Throwable $t) {
            $pdo->rollBack();
            throw $t;
        }
    }

    private static function insertMessage(PDO $pdo, int $ticketId, string $senderType, ?int $senderId, string $body): void
    {
        $stmt = $pdo->prepare('INSERT INTO support_messages (ticket_id, sender_type, sender_id, body, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$ticketId, $senderType, $senderId, $body]);
    }

    public static function getTicketsForUser(int $userId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE user_id = ? ORDER BY updated_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function getTicketForUser(string $code, int $userId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE code = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$code, $userId]);
        $ticket = $stmt->fetch();
        return $ticket ? self::hydrateWithMessages($ticket) : null;
    }

    public static function getTicketByCodeAndEmail(string $code, string $email): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE code = ? AND email = ? LIMIT 1');
        $stmt->execute([$code, $email]);
        $ticket = $stmt->fetch();
        return $ticket ? self::hydrateWithMessages($ticket) : null;
    }

    public static function getTicketByCode(string $code): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE code = ? LIMIT 1');
        $stmt->execute([$code]);
        $ticket = $stmt->fetch();
        return $ticket ? self::hydrateWithMessages($ticket) : null;
    }

    public static function getTicketById(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();
        return $ticket ? self::hydrateWithMessages($ticket) : null;
    }

    private static function hydrateWithMessages(array $ticket): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM support_messages WHERE ticket_id = ? ORDER BY created_at ASC, id ASC');
        $stmt->execute([$ticket['id']]);
        $ticket['messages'] = $stmt->fetchAll() ?: [];
        return $ticket;
    }

    public static function userCanReply(array $ticket): bool
    {
        if ($ticket['status'] === 'closed') {
            return false;
        }
        if (($ticket['user_followups'] ?? 0) >= 2) {
            return false;
        }
        return ($ticket['last_message_by'] ?? 'admin') === 'admin';
    }

    public static function addUserReply(array $ticket, ?int $userId, string $message): ?array
    {
        if (!self::userCanReply($ticket)) {
            return null;
        }
        $message = trim($message);
        if ($message === '') {
            return null;
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            self::insertMessage($pdo, (int)$ticket['id'], 'user', $userId, $message);
            $stmt = $pdo->prepare('UPDATE support_tickets SET user_followups = user_followups + 1, last_message_by = "user", status = "waiting_admin", updated_at = NOW() WHERE id = ?');
            $stmt->execute([$ticket['id']]);
            $pdo->commit();
            return self::getTicketById((int)$ticket['id']);
        } catch (Throwable $t) {
            $pdo->rollBack();
            throw $t;
        }
    }

    public static function addAdminReply(array $ticket, int $adminId, string $message, bool $closeTicket = false): ?array
    {
        $message = trim($message);
        if ($message === '') {
            return null;
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            self::insertMessage($pdo, (int)$ticket['id'], 'admin', $adminId, $message);
            if ($closeTicket) {
                $stmt = $pdo->prepare('UPDATE support_tickets SET status = "closed", last_message_by = "admin", updated_at = NOW(), closed_at = NOW() WHERE id = ?');
            } else {
                $stmt = $pdo->prepare('UPDATE support_tickets SET status = "waiting_user", last_message_by = "admin", updated_at = NOW() WHERE id = ?');
            }
            $stmt->execute([$ticket['id']]);
            $pdo->commit();
            $updated = self::getTicketById((int)$ticket['id']);
        } catch (Throwable $t) {
            $pdo->rollBack();
            throw $t;
        }

        if (!empty($updated)) {
            try {
                Mailer::sendTicketReplyNotification($updated, $message, $closeTicket);
            } catch (Throwable $mailError) {
                error_log('Ticket reply notification error: ' . $mailError->getMessage());
            }
        }

        return $updated ?? null;
    }

    public static function closeTicket(int $ticketId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE support_tickets SET status = "closed", last_message_by = "admin", updated_at = NOW(), closed_at = NOW() WHERE id = ?');
        $stmt->execute([$ticketId]);
    }

    public static function getTicketsForAdmin(?string $status = null): array
    {
        $pdo = Database::pdo();
        if ($status) {
            $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE status = ? ORDER BY updated_at DESC');
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->query('SELECT * FROM support_tickets ORDER BY updated_at DESC');
        }
        return $stmt->fetchAll() ?: [];
    }
}
