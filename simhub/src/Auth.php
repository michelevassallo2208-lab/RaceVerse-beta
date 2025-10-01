<?php
class Auth {
  public const ROLE_ADMIN = 'admin';
  public const ROLE_PRO   = 'pro';
  public const ROLE_GUEST = 'guest';

  public static function start(): void {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  public static function register(string $email, string $password): array {
    self::start();
    $email = strtolower(trim($email));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return [false, 'Inserisci un indirizzo email valido.'];
    }

    if (strlen($password) < 8) {
      return [false, 'La password deve contenere almeno 8 caratteri.'];
    }

    $pdo = Database::pdo();

    try {
      $stmt = $pdo->prepare(
        'INSERT INTO users (email, password_hash, role, subscription_plan, subscription_active) VALUES (?, ?, ?, NULL, 0)'
      );
      $stmt->execute([
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        self::ROLE_GUEST,
      ]);
    } catch (PDOException $e) {
      if ((int) $e->getCode() === 23000) {
        return [false, 'Esiste già un account con questa email.'];
      }
      throw $e;
    }

    self::login($email, $password);

    return [true, null];
  }

  public static function login(string $email, string $password): bool {
    self::start();
    $pdo = Database::pdo();
    $st = $pdo->prepare(
      "SELECT id,email,password_hash,role,subscription_plan,subscription_active FROM users WHERE email=? LIMIT 1"
    );
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
      $_SESSION['user'] = [
        'id' => $u['id'],
        'email' => $u['email'],
        'role' => $u['role'],
        'subscription_plan' => $u['subscription_plan'],
        'subscription_active' => (bool) $u['subscription_active'],
      ];
      return true;
    }
    return false;
  }

  public static function user(): ?array {
    self::start();
    return $_SESSION['user'] ?? null;
  }

  public static function logout(): void {
    self::start();
    $_SESSION = [];
    session_destroy();
  }

  public static function isAdmin(): bool {
    $u = self::user();
    return $u && $u['role'] === self::ROLE_ADMIN;
  }

  public static function isPro(): bool {
    $u = self::user();
    if (!$u) {
      return false;
    }
    if ($u['role'] === self::ROLE_ADMIN) {
      return true;
    }
    if ($u['role'] !== self::ROLE_PRO) {
      return false;
    }
    $plan = $u['subscription_plan'];
    return in_array($plan, ['RaceVerse Pro', 'MetaVerse Pro'], true)
      && $u['subscription_active'];
  }

  public static function isGuest(): bool {
    $u = self::user();
    return $u && $u['role'] === self::ROLE_GUEST;
  }

  public static function hasSetupAccess(): bool {
    return self::isPro();
  }

  public static function roleLabel(?string $role): string {
    return match ($role) {
      self::ROLE_ADMIN => 'Admin',
      self::ROLE_PRO => 'RaceVerse Pro',
      self::ROLE_GUEST, 'user' => 'RaceVerse Guest',
      default => $role ?? 'Sconosciuto',
    };
  }
}
