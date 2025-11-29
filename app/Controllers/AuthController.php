<?php

namespace App\Controllers;

use App\Models\AuthModel;

/**
 * AuthController - Controlador de autenticación
 * Maneja login, registro, recuperación y cambio de contraseña
 */
class AuthController
{
    private $model;

    public function __construct()
    {
        $this->model = new AuthModel();
    }

    /**
     * Muestra el formulario de login
     */
    public function showLogin()
    {
        // Si ya está autenticado, redirigir al dashboard
        if (isAuthenticated()) {
            redirect('/dashboard');
        }

        $this->render('auth/login', [
            'title' => 'Iniciar Sesión'
        ]);
    }

    /**
     * Procesa el login
     */
    public function login()
    {
        // Validar CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Token de seguridad inválido');
            redirect('/auth/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validaciones
        if (empty($email) || empty($password)) {
            setFlash('danger', 'Por favor completa todos los campos');
            redirect('/auth/login');
        }

        if (!validateEmail($email)) {
            setFlash('danger', 'Email inválido');
            redirect('/auth/login');
        }

        // Buscar usuario
        $user = $this->model->findByEmail($email);

        if (!$user || !verifyPassword($password, $user['password_hash'])) {
            setFlash('danger', 'Credenciales incorrectas');
            redirect('/auth/login');
        }

        // Crear sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'nombre' => $user['nombre'],
            'rol_id' => $user['rol_id']
        ];

        // Actualizar último login
        $this->model->updateLastLogin($user['id']);

        setFlash('success', '¡Bienvenido, ' . $user['nombre'] . '!');
        redirect('/dashboard');
    }

    /**
     * Muestra el formulario de registro
     */
    public function showRegister()
    {
        if (isAuthenticated()) {
            redirect('/dashboard');
        }

        $this->render('auth/register', [
            'title' => 'Registro'
        ]);
    }

    /**
     * Procesa el registro
     */
    public function register()
    {
        // Validar CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Token de seguridad inválido');
            redirect('/auth/register');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validaciones
        $errors = [];

        if (empty($nombre)) {
            $errors[] = 'El nombre es requerido';
        }

        if (empty($email) || !validateEmail($email)) {
            $errors[] = 'Email inválido';
        }

        if ($this->model->emailExists($email)) {
            $errors[] = 'Este email ya está registrado';
        }

        if (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden';
        }

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('/auth/register');
        }

        // Crear usuario
        try {
            $userId = $this->model->create([
                'nombre' => $nombre,
                'email' => $email,
                'password_hash' => hashPassword($password),
                'rol_id' => 1 // Usuario normal
            ]);

            setFlash('success', 'Registro exitoso. Por favor inicia sesión.');
            redirect('/auth/login');
        } catch (\Exception $e) {
            setFlash('danger', 'Error al crear la cuenta. Por favor intenta de nuevo.');
            redirect('/auth/register');
        }
    }

    /**
     * Muestra el formulario de recuperación
     */
    public function showRecover()
    {
        if (isAuthenticated()) {
            redirect('/dashboard');
        }

        $this->render('auth/recover', [
            'title' => 'Recuperar Contraseña'
        ]);
    }

    /**
     * Procesa la recuperación de contraseña
     */
    public function recover()
    {
        // Validar CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Token de seguridad inválido');
            redirect('/auth/recover');
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !validateEmail($email)) {
            setFlash('danger', 'Por favor ingresa un email válido');
            redirect('/auth/recover');
        }

        // Verificar si el usuario existe
        $user = $this->model->findByEmail($email);

        if ($user) {
            // Generar token
            $token = $this->model->createResetToken($email);

            // Aquí se enviaría el email con el link
            // Por ahora, mostraremos el link en la sesión (solo para desarrollo)
            $resetLink = url("/auth/reset/{$token}");
            
            // En producción: enviar email
            // sendEmail($email, 'Recuperar Contraseña', "Link: {$resetLink}");
            
            setFlash('info', "Link de recuperación (desarrollo): <a href='{$resetLink}'>{$resetLink}</a>");
        } else {
            // No revelar si el email existe o no (seguridad)
            setFlash('info', 'Si el email existe, recibirás un link de recuperación');
        }

        redirect('/auth/recover');
    }

    /**
     * Muestra el formulario de restablecimiento
     */
    public function showReset($token)
    {
        if (isAuthenticated()) {
            redirect('/dashboard');
        }

        // Validar token
        $user = $this->model->validateResetToken($token);

        if (!$user) {
            setFlash('danger', 'El link de recuperación es inválido o ha expirado');
            redirect('/auth/recover');
        }

        $this->render('auth/reset', [
            'title' => 'Restablecer Contraseña',
            'token' => $token
        ]);
    }

    /**
     * Procesa el restablecimiento de contraseña
     */
    public function reset()
    {
        // Validar CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Token de seguridad inválido');
            redirect('/auth/recover');
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validaciones
        if (strlen($password) < 6) {
            setFlash('danger', 'La contraseña debe tener al menos 6 caracteres');
            redirect("/auth/reset/{$token}");
        }

        if ($password !== $confirmPassword) {
            setFlash('danger', 'Las contraseñas no coinciden');
            redirect("/auth/reset/{$token}");
        }

        // Restablecer contraseña
        $success = $this->model->resetPassword($token, hashPassword($password));

        if ($success) {
            setFlash('success', 'Contraseña restablecida exitosamente. Por favor inicia sesión.');
            redirect('/auth/login');
        } else {
            setFlash('danger', 'El link de recuperación es inválido o ha expirado');
            redirect('/auth/recover');
        }
    }

    /**
     * Cambia la contraseña del usuario autenticado
     */
    public function changePassword()
    {
        requireAuth();

        // Validar CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Token de seguridad inválido');
            redirect('/dashboard');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validaciones
        if (strlen($newPassword) < 6) {
            setFlash('danger', 'La nueva contraseña debe tener al menos 6 caracteres');
            redirect('/dashboard');
        }

        if ($newPassword !== $confirmPassword) {
            setFlash('danger', 'Las contraseñas no coinciden');
            redirect('/dashboard');
        }

        // Verificar contraseña actual
        $user = $this->model->findById(userId());

        if (!$user || !verifyPassword($currentPassword, $user['password_hash'])) {
            setFlash('danger', 'La contraseña actual es incorrecta');
            redirect('/dashboard');
        }

        // Actualizar contraseña
        $this->model->updatePassword(userId(), hashPassword($newPassword));

        setFlash('success', 'Contraseña actualizada exitosamente');
        redirect('/dashboard');
    }

    /**
     * Cierra la sesión
     */
    public function logout()
    {
        session_destroy();
        redirect('/auth/login');
    }

    /**
     * Renderiza una vista
     */
    private function render($view, $data = [])
    {
        extract($data);
        
        ob_start();
        require_once __DIR__ . "/../Views/{$view}.php";
        $content = ob_get_clean();
        
        require_once __DIR__ . "/../Views/layouts/app.php";
    }
}
