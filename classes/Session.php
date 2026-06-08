<?php
/**
 * Session — Gestion sécurisée des sessions PHP
 * Concept POO : Singleton, Encapsulation
 */
class Session
{
    private static ?Session $instance = null;

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Strict');
            session_start();
        }
        if (!isset($_SESSION['_last_regen'])) {
            session_regenerate_id(true);
            $_SESSION['_last_regen'] = time();
        } elseif (time() - $_SESSION['_last_regen'] > 300) {
            session_regenerate_id(true);
            $_SESSION['_last_regen'] = time();
        }
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
    }

    public static function getInstance(): static
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function set(string $key, mixed $value): void   { $_SESSION[$key] = $value; }
    public function get(string $key, mixed $def = null): mixed { return $_SESSION[$key] ?? $def; }
    public function has(string $key): bool   { return isset($_SESSION[$key]); }
    public function remove(string $key): void { unset($_SESSION[$key]); }

    public function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        self::$instance = null;
    }

    public function isAuthenticated(): bool { return $this->has('user_id') && $this->has('user_role'); }
    public function getUserId(): int        { return (int)$this->get('user_id', 0); }
    public function getUserRole(): string   { return (string)$this->get('user_role', ''); }
    public function getCsrfToken(): string  { return (string)($_SESSION[CSRF_TOKEN_NAME] ?? ''); }

    public function verifyCsrfToken(string $token): bool
    {
        return hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
    }

    public function addFlash(string $type, string $msg): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $msg];
    }

    public function getFlashes(): array
    {
        $f = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $f;
    }
}
