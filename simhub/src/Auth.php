<?php
class Auth {
  private const SESSION_LIFETIME = 3600; // 1 ora

  public static function start(): void {
    if (session_status() === PHP_SESSION_NONE) {
      $script = $_SERVER['SCRIPT_NAME'] ?? '/';
      $dir = str_replace('\\', '/', dirname($script));
      $path = ($dir === '/' || $dir === '\\' || $dir === '.') ? '/' : $dir;
      session_set_cookie_params([
        'lifetime' => self::SESSION_LIFETIME,
        'path' => $path,
        'httponly' => true,
        'samesite' => 'Lax',
      ]);
      session_start();
    }
  }
  public static function login(string $email, string $password): bool {
    self::start();
    $pdo = Database::pdo();
    $st = $pdo->prepare("SELECT id,email,password_hash,role,subscription_plan,subscription_active FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
      session_regenerate_id(true);
      $expiry = time() + self::SESSION_LIFETIME;
      $_SESSION['user'] = [
        'id'=>$u['id'],
        'email'=>$u['email'],
        'role'=>$u['role'],
        'subscription_plan'=>$u['subscription_plan'],
        'subscription_active'=>(bool)$u['subscription_active'],
        'expires_at'=>$expiry,
      ];
      return true;
    }
    return false;
  }
  public static function user(): ?array {
    self::start();
    if (empty($_SESSION['user'])) {
      return null;
    }
    $expiresAt = $_SESSION['user']['expires_at'] ?? 0;
    if ($expiresAt && $expiresAt < time()) {
      self::logout();
      return null;
    }
    $_SESSION['user']['expires_at'] = time() + self::SESSION_LIFETIME;
    return $_SESSION['user'];
  }
  public static function logout(): void {
    self::start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/', $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? false);
    }
    session_destroy();
  }
  public static function isAdmin(): bool { $u=self::user(); return $u && $u['role']==='admin'; }
  public static function isPro(): bool {
    $u=self::user(); return $u && $u['subscription_plan']==='RaceVerse PRO' && $u['subscription_active'];
  }
}
