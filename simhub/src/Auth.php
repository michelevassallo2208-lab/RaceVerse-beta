<?php
class Auth {
  private const SESSION_TTL = 3600;

  public static function start(): void {
    if (session_status()===PHP_SESSION_NONE) {
      session_start();
    }

    if (!empty($_SESSION['user'])) {
      $expires = $_SESSION['user_expires_at'] ?? 0;
      if ($expires < time()) {
        self::terminateSession();
      } else {
        $_SESSION['user_expires_at'] = time() + self::SESSION_TTL;
      }
    }
  }
  public static function login(string $email, string $password): bool {
    self::start();
    $pdo = Database::pdo();
    $st = $pdo->prepare("SELECT id,email,password_hash,role,subscription_plan,subscription_active FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
      $_SESSION['user'] = [
        'id'=>$u['id'],
        'email'=>$u['email'],
        'role'=>$u['role'],
        'subscription_plan'=>$u['subscription_plan'],
        'subscription_active'=>(bool)$u['subscription_active'],
      ];
      $_SESSION['user_expires_at'] = time() + self::SESSION_TTL;
      return true;
    }
    return false;
  }
  public static function user(): ?array { self::start(); return $_SESSION['user'] ?? null; }
  public static function logout(): void {
    self::start();
    self::terminateSession();
  }
  public static function isAdmin(): bool { $u=self::user(); return $u && $u['role']==='admin'; }
  public static function isPro(): bool {
    $u=self::user(); return $u && $u['subscription_plan']==='MetaVerse Pro' && $u['subscription_active'];
  }

  private static function terminateSession(): void {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_unset();
      session_destroy();
    }
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }
}
