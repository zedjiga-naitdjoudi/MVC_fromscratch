<?php

namespace App\Core;

class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', 1); //PHP rejettera session id non généré
            ini_set('session.cookie_httponly', 1 ); //empêche JavaScript d’accéder au cookie de session (réduction XSS impact).
            ini_set('session.use_only_cookies', 1); //interdit l’utilisation de session id en URL.
            session_start();
        }
    }

    public static function regenerateId(): void
    {
        session_regenerate_id(true);
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
             );
        }
        session_destroy();
    }

    public static function generateCsrfToken(): string
    {
        if (empty(self::get('csrf_token'))) {
            try {
                self::set('csrf_token', bin2hex(random_bytes(32)));
            } catch (\Exception $e) {
                error_log("Erreur de génération de jeton CSRF: " . $e->getMessage());
                return '';
            }
        }
        return self::get('csrf_token');
    }

    public static function verifyCsrfToken(string $token): bool
    {
        $sessionToken = self::get('csrf_token');
        return is_string($sessionToken) && is_string($token) && hash_equals($sessionToken, $token);
    }
    public static function getFlash(string $key): ?string
{
    $value = self::get($key);
    if ($value !== null) {
        self::set($key, null); 
    }
    return $value;
}

}
