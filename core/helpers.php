<?php

/**
 * Funciones auxiliares globales
 */

/**
 * Redirige a una URL
 */
function redirect($path)
{
    header("Location: " . APP_URL . "/" . ltrim($path, '/'));
    exit;
}

/**
 * Establece un mensaje flash en la sesión
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtiene y elimina un mensaje flash de la sesión
 */
function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Verifica si el usuario está autenticado
 */
function isAuthenticated()
{
    return isset($_SESSION['user_id']);
}

/**
 * Obtiene el ID del usuario autenticado
 */
function userId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtiene el usuario completo de la sesión
 */
function user()
{
    return $_SESSION['user'] ?? null;
}

/**
 * Verifica si el usuario tiene un rol específico
 */
function hasRole($rol)
{
    return isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] == $rol;
}

/**
 * Genera un token CSRF
 */
function generateCsrfToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida un token CSRF
 */
function validateCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Escapa HTML para prevenir XSS
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Genera una URL completa
 */
function url($path = '')
{
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Genera una URL de asset
 */
function asset($path)
{
    return APP_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Middleware simple para proteger rutas
 */
function requireAuth()
{
    if (!isAuthenticated()) {
        setFlash('warning', 'Debes iniciar sesión para acceder a esta página');
        redirect('/auth/login');
    }
}

/**
 * Middleware para requerir un rol específico
 */
function requireRole($rolId)
{
    requireAuth();
    if (!hasRole($rolId)) {
        setFlash('danger', 'No tienes permisos para acceder a esta página');
        redirect('/dashboard');
    }
}

/**
 * Valida y limpia un email
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Genera un hash de contraseña seguro
 */
function hashPassword($password)
{
    return password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
}

/**
 * Verifica una contraseña contra un hash
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Genera un token aleatorio seguro
 */
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Formatea una fecha
 */
function formatDate($date, $format = 'd/m/Y')
{
    return date($format, strtotime($date));
}

/**
 * Formatea una fecha con hora
 */
function formatDateTime($date, $format = 'd/m/Y H:i')
{
    return date($format, strtotime($date));
}

/**
 * Formatea un número como moneda
 */
function formatMoney($amount, $currency = '$')
{
    return $currency . ' ' . number_format($amount, 2, '.', ',');
}

/**
 * Obtiene el nombre del rol
 */
function getRoleName($rolId)
{
    $roles = [
        1 => 'Usuario',
        2 => 'Vendedor',
        3 => 'Administrador'
    ];
    return $roles[$rolId] ?? 'Desconocido';
}
