<?php

/**
 * Funciones auxiliares globales
 */

/**
 * Redirige a una URL
 */
function redirect($path)
{
    // Limpiar cualquier salida previa
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Si el path ya es una URL completa (empieza con http:// o https://), usarla directamente
    if (preg_match('/^https?:\/\//', $path)) {
        header("Location: " . $path, true, 302);
        exit;
    }
    
    // Si no, construir la URL completa
    header("Location: " . APP_URL . "/" . ltrim($path, '/'), true, 302);
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
 * Verifica si el usuario es administrador
 */
function isAdmin()
{
    return isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'admin';
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

/**
 * Verifica si el usuario tiene un permiso específico
 * (Placeholder para sistema de permisos - Módulo 6)
 */
function can($permission)
{
    // Por ahora todos los usuarios autenticados pueden todo
    // En Módulo 6 se implementará sistema granular de permisos
    if (!isAuthenticated()) {
        return false;
    }
    
    // Administradores tienen todos los permisos
    if (hasRole('admin')) {
        return true;
    }
    
    // Mapeo básico de permisos por rol
    $rolePermissions = [
        'administrador' => [
            'ver_lotes', 'crear_lotes', 'editar_lotes', 'eliminar_lotes',
            'ver_clientes', 'crear_clientes', 'editar_clientes', 'eliminar_clientes',
            'ver_proyectos', 'crear_proyectos', 'editar_proyectos', 'eliminar_proyectos',
            'crear_amortizacion', 'ver_amortizacion', 'editar_amortizacion',
            'registrar_pagos', 'ver_pagos', 'eliminar_pagos',
            'ver_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios',
            'ver_reportes', 'exportar_datos'
        ],
        'vendedor' => [
            'ver_lotes', 'crear_lotes', 'editar_lotes', 'eliminar_lotes',
            'ver_clientes', 'crear_clientes',
            'ver_proyectos', 'crear_proyectos', 'editar_proyectos',
            'crear_amortizacion', 'ver_amortizacion', 'editar_amortizacion',
            'registrar_pagos', 'ver_pagos',
            'ver_reportes'
        ],
        'consulta' => [
            'ver_lotes', 'ver_clientes', 'ver_proyectos',
            'ver_amortizacion', 'ver_pagos', 'ver_reportes'
        ],
        'usuario' => [
            'ver_lotes', 'ver_clientes', 'ver_proyectos',
            'ver_amortizacion', 'ver_pagos'
        ]
    ];
    
    $userRole = $_SESSION['user']['rol'] ?? 'usuario';
    $permissions = $rolePermissions[$userRole] ?? [];
    
    return in_array($permission, $permissions);
}

/**
 * Genera campo CSRF oculto para formularios
 */
function csrfField()
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Obtiene la clase CSS para badges de estado de lote
 */
function statusClass($estado)
{
    $classes = [
        'disponible' => 'bg-success',
        'reservado' => 'bg-warning text-dark',
        'vendido' => 'bg-primary',
        'bloqueado' => 'bg-secondary'
    ];
    
    return $classes[$estado] ?? 'bg-secondary';
}

/**
 * Obtiene el valor anterior de un campo (para re-población de formularios)
 * Útil después de errores de validación
 */
function old($key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}

/**
 * Guarda datos del POST en la sesión para re-población
 */
function saveOldInput($data)
{
    $_SESSION['old'] = $data;
}

/**
 * Limpia los datos antiguos del formulario
 */
function clearOldInput()
{
    unset($_SESSION['old']);
}

/**
 * Obtiene un valor del POST de forma segura
 */
function post($key, $default = '')
{
    return $_POST[$key] ?? $default;
}

/**
 * Genera opciones para un select HTML
 * 
 * @param array $options Array de opciones [value => label]
 * @param mixed $selected Valor seleccionado
 * @param bool $includeEmpty Si incluir opción vacía
 * @param string $emptyText Texto de la opción vacía
 * @return string HTML de las opciones
 */
function selectOptions($options, $selected = null, $includeEmpty = true, $emptyText = 'Seleccione...')
{
    $html = '';
    
    if ($includeEmpty) {
        $html .= '<option value="">' . e($emptyText) . '</option>';
    }
    
    foreach ($options as $value => $label) {
        $isSelected = ($selected !== null && $selected == $value) ? 'selected' : '';
        $html .= '<option value="' . e($value) . '" ' . $isSelected . '>' . e($label) . '</option>';
    }
    
    return $html;
}

/**
 * Verifica si existe un error de validación para un campo
 * 
 * @param string $field Nombre del campo
 * @return bool
 */
function hasError($field)
{
    return isset($_SESSION['errors'][$field]);
}

/**
 * Obtiene el mensaje de error de validación para un campo
 * 
 * @param string $field Nombre del campo
 * @return string|null
 */
function getError($field)
{
    return $_SESSION['errors'][$field] ?? null;
}

/**
 * Guarda errores de validación en la sesión
 * 
 * @param array $errors Array de errores [campo => mensaje]
 */
function saveErrors($errors)
{
    $_SESSION['errors'] = $errors;
}

/**
 * Limpia los errores de validación de la sesión
 */
function clearErrors()
{
    unset($_SESSION['errors']);
}
