<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * PerfilController - Gestión de Perfil de Usuario
 * Módulo 8: Permite al usuario ver y actualizar sus datos personales y contraseña
 */
class PerfilController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Muestra el perfil del usuario autenticado
     * GET /perfil
     */
    public function index()
    {
        $this->requireAuth();

        // Obtener usuario autenticado
        $user = user();

        // Obtener datos completos del usuario desde la base de datos
        $userData = $this->userModel->findById($user['id']);

        if (!$userData) {
            $this->flash('error', 'No se pudo cargar la información del perfil');
            $this->redirect('/dashboard');
            return;
        }

        // Si el usuario es vendedor, obtener información adicional
        $vendedorInfo = null;
        if ($userData['rol'] === 'vendedor') {
            $db = \Database::getInstance();
            $vendedorInfo = $db->fetch(
                "SELECT * FROM vendedores WHERE user_id = ?",
                [$userData['id']]
            );
        }

        $this->view('perfil/index', [
            'title' => 'Mi Perfil de Usuario',
            'user' => $userData,
            'vendedorInfo' => $vendedorInfo
        ]);
    }

    /**
     * Actualiza los datos personales del usuario
     * POST /perfil/update
     */
    public function updateData()
    {
        $this->requireAuth();

        // Validar CSRF
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token de seguridad inválido');
            $this->redirect('/perfil');
            return;
        }

        try {
            $user = user();
            $userId = $user['id'];

            // Validar datos requeridos
            if (empty($_POST['nombre'])) {
                throw new \Exception('El nombre es obligatorio');
            }

            if (empty($_POST['email'])) {
                throw new \Exception('El email es obligatorio');
            }

            // Validar formato de email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('El formato del email no es válido');
            }

            // Verificar si el email ya existe (excepto el del usuario actual)
            $existingUser = $this->userModel->findByEmail($_POST['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                throw new \Exception('El email ya está siendo utilizado por otro usuario');
            }

            // Preparar datos para actualizar
            $data = [
                'nombre' => trim($_POST['nombre']),
                'email' => trim($_POST['email'])
            ];

            // Actualizar usuario
            $this->userModel->update($userId, $data);

            // Actualizar sesión con los nuevos datos
            $_SESSION['user']['nombre'] = $data['nombre'];
            $_SESSION['user']['email'] = $data['email'];

            \Logger::info("Perfil actualizado", [
                'user_id' => $userId,
                'nombre' => $data['nombre'],
                'email' => $data['email']
            ]);

            $this->flash('success', 'Perfil actualizado correctamente');
            $this->redirect('/perfil');

        } catch (\Exception $e) {
            $this->flash('error', 'Error al actualizar perfil: ' . $e->getMessage());
            $this->redirect('/perfil');
        }
    }

    /**
     * Actualiza la contraseña del usuario
     * POST /perfil/update-password
     */
    public function updatePassword()
    {
        $this->requireAuth();

        // Validar CSRF
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token de seguridad inválido');
            $this->redirect('/perfil');
            return;
        }

        try {
            $user = user();
            $userId = $user['id'];

            // Validar campos requeridos
            if (empty($_POST['contrasena_actual'])) {
                throw new \Exception('La contraseña actual es obligatoria');
            }

            if (empty($_POST['nueva_contrasena'])) {
                throw new \Exception('La nueva contraseña es obligatoria');
            }

            if (empty($_POST['confirmar_contrasena'])) {
                throw new \Exception('Debe confirmar la nueva contraseña');
            }

            // Validar que la nueva contraseña tenga al menos 6 caracteres
            if (strlen($_POST['nueva_contrasena']) < 6) {
                throw new \Exception('La nueva contraseña debe tener al menos 6 caracteres');
            }

            // Validar que las contraseñas nuevas coincidan
            if ($_POST['nueva_contrasena'] !== $_POST['confirmar_contrasena']) {
                throw new \Exception('La nueva contraseña y su confirmación no coinciden');
            }

            // Obtener usuario completo de la base de datos
            $userData = $this->userModel->findById($userId);

            // Verificar que la contraseña actual sea correcta
            if (!password_verify($_POST['contrasena_actual'], $userData['password'])) {
                throw new \Exception('La contraseña actual es incorrecta');
            }

            // Validar que la nueva contraseña sea diferente a la actual
            if (password_verify($_POST['nueva_contrasena'], $userData['password'])) {
                throw new \Exception('La nueva contraseña debe ser diferente a la actual');
            }

            // Generar hash seguro de la nueva contraseña
            $newPasswordHash = password_hash($_POST['nueva_contrasena'], PASSWORD_BCRYPT, ['cost' => 12]);

            // Actualizar contraseña en la base de datos
            $this->userModel->updatePassword($userId, $newPasswordHash);

            \Logger::info("Contraseña actualizada", [
                'user_id' => $userId,
                'email' => $userData['email']
            ]);

            $this->flash('success', 'Contraseña actualizada correctamente');
            $this->redirect('/perfil');

        } catch (\Exception $e) {
            $this->flash('error', 'Error al actualizar contraseña: ' . $e->getMessage());
            $this->redirect('/perfil');
        }
    }
}
