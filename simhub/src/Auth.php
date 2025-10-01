<?php
class Auth {
  protected static ?string $lastError = null;

  public static function start(): void { if (session_status()===PHP_SESSION_NONE) session_start(); }
  public static function login(string $email, string $password): bool {
    self::start();
    self::$lastError = null;
    $pdo = Database::pdo();
    $st = $pdo->prepare("SELECT id,email,password_hash,first_name,last_name,role,subscription_plan,subscription_active,email_verified_at FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
      if (empty($u['email_verified_at'])) {
        self::$lastError = 'unverified';
        return false;
      }
      $_SESSION['user'] = [
        'id'=>$u['id'],
        'email'=>$u['email'],
        'first_name'=>$u['first_name'],
        'last_name'=>$u['last_name'],
        'role'=>$u['role'],
        'subscription_plan'=>$u['subscription_plan'],
        'subscription_active'=>(bool)$u['subscription_active'],
      ];
      return true;
    }
    self::$lastError = 'invalid';
    return false;
  }
  public static function lastError(): ?string { return self::$lastError; }
  public static function user(): ?array { self::start(); return $_SESSION['user'] ?? null; }
  public static function logout(): void { self::start(); $_SESSION=[]; session_destroy(); }
  public static function isAdmin(): bool { $u=self::user(); return $u && $u['role']==='admin'; }
  public static function isPro(): bool {
    $u=self::user(); return $u && $u['subscription_plan']==='Raceverse Pro' && $u['subscription_active'];
  }
}
