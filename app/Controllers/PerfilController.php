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
     * 
     * Realiza una consulta condicional JOIN a la tabla vendedores
     * si el usuario tiene rol 'vendedor' o 'administrador'
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

        // Consulta condicional: JOIN a vendedores si el rol es 'vendedor' o 'administrador'
        $perfil_vendedor = null;
        
        if ($userData['rol'] === 'vendedor' || $userData['rol'] === 'administrador') {
            $db = \Database::getInstance();
            
            // Consulta con JOIN para obtener datos de vendedor asociado al user_id
            $sql = "SELECT 
                        v.*,
                        u.email as user_email,
                        u.nombre as user_nombre
                    FROM vendedores v
                    INNER JOIN users u ON v.user_id = u.id
                    WHERE v.user_id = ?
                    LIMIT 1";
                    
            $perfil_vendedor = $db->fetch($sql, [$userData['id']]);
        }

        // Variables para la vista
        $this->view('perfil/index', [
            'title' => 'Mi Perfil de Usuario',
            'user' => $userData,                    // Datos de users
            'perfil_vendedor' => $perfil_vendedor   // Datos de vendedores (null si no es vendedor)
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
     * 
     * Implementa validación estricta de la contraseña actual
     * antes de aplicar el hash a la nueva contraseña
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

            // Obtener usuario completo con contraseña de la base de datos
            $db = \Database::getInstance();
            $userData = $db->fetch(
                "SELECT id, email, nombre, password FROM users WHERE id = ?",
                [$userId]
            );

            if (!$userData) {
                throw new \Exception('Usuario no encontrado');
            }

            // VALIDACIÓN ESTRICTA: Verificar que la contraseña actual sea correcta
            if (!password_verify($_POST['contrasena_actual'], $userData['password'])) {
                \Logger::warning("Intento fallido de cambio de contraseña", [
                    'user_id' => $userId,
                    'email' => $userData['email'],
                    'reason' => 'Contraseña actual incorrecta'
                ]);
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

            \Logger::info("Contraseña actualizada exitosamente", [
                'user_id' => $userId,
                'email' => $userData['email']
            ]);

            $this->flash('success', 'Contraseña actualizada correctamente. Por seguridad, se recomienda cerrar sesión e iniciar sesión nuevamente.');
            $this->redirect('/perfil');

        } catch (\Exception $e) {
            $this->flash('error', 'Error al actualizar contraseña: ' . $e->getMessage());
            $this->redirect('/perfil');
        }
    }

    /**
     * Actualiza los datos de contacto del vendedor
     * POST /perfil/update-vendedor
     * 
     * Solo disponible para usuarios con rol 'vendedor' o 'administrador'
     * que tengan un registro asociado en la tabla vendedores
     */
    public function updateVendedor()
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

            // Verificar que el usuario sea vendedor o administrador
            if ($user['rol'] !== 'vendedor' && $user['rol'] !== 'administrador') {
                throw new \Exception('No tienes permisos para actualizar datos de vendedor');
            }

            $db = \Database::getInstance();

            // Verificar que exista un registro de vendedor asociado
            $vendedor = $db->fetch(
                "SELECT id, user_id FROM vendedores WHERE user_id = ?",
                [$userId]
            );

            if (!$vendedor) {
                throw new \Exception('No se encontró un perfil de vendedor asociado a tu usuario');
            }

            // Validar campos requeridos
            if (empty($_POST['celular'])) {
                throw new \Exception('El celular corporativo es obligatorio');
            }

            // Preparar datos para actualizar
            $data = [
                'telefono' => !empty($_POST['telefono']) ? trim($_POST['telefono']) : null,
                'celular' => trim($_POST['celular']),
                'direccion' => !empty($_POST['direccion']) ? trim($_POST['direccion']) : null,
                'ciudad' => !empty($_POST['ciudad']) ? trim($_POST['ciudad']) : null
            ];

            // Construir consulta de actualización
            $sql = "UPDATE vendedores 
                    SET telefono = ?, 
                        celular = ?, 
                        direccion = ?, 
                        ciudad = ?,
                        updated_at = NOW()
                    WHERE id = ?";

            $params = [
                $data['telefono'],
                $data['celular'],
                $data['direccion'],
                $data['ciudad'],
                $vendedor['id']
            ];

            $db->execute($sql, $params);

            \Logger::info("Datos de contacto de vendedor actualizados", [
                'user_id' => $userId,
                'vendedor_id' => $vendedor['id'],
                'celular' => $data['celular']
            ]);

            $this->flash('success', 'Datos de contacto actualizados correctamente');
            $this->redirect('/perfil');

        } catch (\Exception $e) {
            $this->flash('error', 'Error al actualizar datos de vendedor: ' . $e->getMessage());
            $this->redirect('/perfil');
        }
    }
}
