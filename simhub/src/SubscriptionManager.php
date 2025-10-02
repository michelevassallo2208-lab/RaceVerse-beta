<?php
require_once __DIR__ . '/Database.php';

class SubscriptionManager
{
    private const PLANS = [
        'pro_monthly' => [
            'code' => 'pro_monthly',
            'label' => 'Mensile',
            'name' => 'RaceVerse PRO (Mensile)',
            'price_eur' => 2.99,
            'duration_days' => 30,
        ],
        'pro_quarterly' => [
            'code' => 'pro_quarterly',
            'label' => 'Trimestrale',
            'name' => 'RaceVerse PRO (Trimestrale)',
            'price_eur' => 7.97,
            'duration_days' => 90,
        ],
        'pro_semiannual' => [
            'code' => 'pro_semiannual',
            'label' => 'Semestrale',
            'name' => 'RaceVerse PRO (Semestrale)',
            'price_eur' => 15.00,
            'duration_days' => 180,
        ],
        'pro_annual' => [
            'code' => 'pro_annual',
            'label' => 'Annuale',
            'name' => 'RaceVerse PRO (Annuale)',
            'price_eur' => 25.00,
            'duration_days' => 365,
        ],
    ];

    public static function allPlans(): array
    {
        return self::PLANS;
    }

    public static function getPlan(string $code): ?array
    {
        return self::PLANS[$code] ?? null;
    }

    public static function activate(int $userId, string $planCode, string $paymentMethod): ?array
    {
        $plan = self::getPlan($planCode);
        if (!$plan) {
            return null;
        }

        $pdo = Database::pdo();
        $now = new DateTimeImmutable('now');
        $started = $now->format('Y-m-d H:i:s');
        $renew = $now->modify('+' . $plan['duration_days'] . ' days')->format('Y-m-d H:i:s');

        $st = $pdo->prepare('UPDATE users SET subscription_plan = ?, subscription_active = 1, subscription_started_at = ?, subscription_renews_at = ?, subscription_payment_method = ?, subscription_cancel_at_period_end = 0 WHERE id = ?');
        $st->execute([
            $plan['name'],
            $started,
            $renew,
            $paymentMethod,
            $userId,
        ]);

        return self::fetchUser($userId);
    }

    public static function fetchUser(int $userId): ?array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT id, email, role, subscription_plan, subscription_active, subscription_started_at, subscription_renews_at, subscription_payment_method, subscription_cancel_at_period_end FROM users WHERE id = ? LIMIT 1');
        $st->execute([$userId]);
        $user = $st->fetch();
        return $user ?: null;
    }

    public static function normalizeUser(array $user): array
    {
        if (!empty($user['subscription_active']) && !empty($user['subscription_renews_at'])) {
            $renewTs = strtotime($user['subscription_renews_at']);
            if ($renewTs !== false && $renewTs <= time()) {
                $existing = $user;
                $user = self::deactivate($user['id']);
                if (!empty($existing['email']) && empty($user['email'])) {
                    $user['email'] = $existing['email'];
                }
                if (!empty($existing['role']) && empty($user['role'])) {
                    $user['role'] = $existing['role'];
                }
            }
        }
        return $user;
    }

    public static function deactivate(int $userId): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('UPDATE users SET subscription_plan = ?, subscription_active = 0, subscription_started_at = NULL, subscription_renews_at = NULL, subscription_payment_method = NULL WHERE id = ?');
        $st->execute(['RaceVerse BASIC', $userId]);
        $user = self::fetchUser($userId);
        if ($user) {
            $user['subscription_active'] = (bool)($user['subscription_active'] ?? false);
        }
        return $user ?? [
            'id' => $userId,
            'subscription_plan' => 'RaceVerse BASIC',
            'subscription_active' => false,
            'subscription_started_at' => null,
            'subscription_renews_at' => null,
            'subscription_payment_method' => null,
            'subscription_cancel_at_period_end' => 0,
            'email' => null,
            'role' => 'user',
        ];
    }
}
