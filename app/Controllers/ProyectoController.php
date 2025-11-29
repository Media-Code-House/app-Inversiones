<?php

namespace App\Controllers;

use App\Models\ProyectoModel;

/**
 * ProyectoController - Controlador de Proyectos
 * Maneja el CRUD completo de proyectos
 */
class ProyectoController extends Controller
{
    private $proyectoModel;

    public function __construct()
    {
        $this->proyectoModel = new ProyectoModel();
    }

    /**
     * Muestra el listado de proyectos
     * GET /proyectos
     */
    public function index()
    {
        $this->requireAuth();

        // Obtener filtros
        $filtros = [
            'busqueda' => $_GET['busqueda'] ?? '',
            'estado' => $_GET['estado'] ?? ''
        ];

        // Obtener proyectos filtrados
        $proyectos = $this->proyectoModel->getAll($filtros);

        $this->view('proyectos/index', [
            'title' => 'Gestión de Proyectos',
            'proyectos' => $proyectos,
            'filtros' => $filtros,
            'estados' => PROYECTO_ESTADOS
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo proyecto
     * GET /proyectos/create
     */
    public function create()
    {
        $this->requireAuth();

        $this->view('proyectos/create', [
            'title' => 'Crear Proyecto',
            'estados' => PROYECTO_ESTADOS
        ]);
    }

    /**
     * Guarda un nuevo proyecto
     * POST /proyectos/store
     */
    public function store()
    {
        $this->requireAuth();

        // Validar CSRF
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token de seguridad inválido');
            $this->redirect('/proyectos/create');
            return;
        }

        // Obtener datos del formulario
        $postData = $_POST;

        // Guardar datos para re-población en caso de error
        saveOldInput($postData);

        // Validaciones
        $errores = [];

        // Validar código (requerido, único)
        if (empty($postData['codigo'])) {
            $errores[] = 'El código del proyecto es obligatorio';
        } elseif ($this->proyectoModel->codigoExists($postData['codigo'])) {
            $errores[] = 'El código del proyecto ya existe';
        }

        // Validar nombre (requerido)
        if (empty($postData['nombre'])) {
            $errores[] = 'El nombre del proyecto es obligatorio';
        }

        // Validar ubicación (requerida)
        if (empty($postData['ubicacion'])) {
            $errores[] = 'La ubicación es obligatoria';
        }

        // Validar estado
        if (empty($postData['estado']) || !array_key_exists($postData['estado'], PROYECTO_ESTADOS)) {
            $errores[] = 'Debe seleccionar un estado válido';
        }

        // Si hay errores, retornar
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $this->redirect('/proyectos/create');
            return;
        }

        // Manejar subida de imagen del plano
        $planoImagen = null;
        if (isset($_FILES['plano_imagen']) && $_FILES['plano_imagen']['error'] === UPLOAD_ERR_OK) {
            $planoImagen = $this->uploadImage($_FILES['plano_imagen'], 'planos');
            
            if ($planoImagen === false) {
                $errores[] = 'Error al subir la imagen del plano';
                $_SESSION['errores'] = $errores;
                $this->redirect('/proyectos/create');
                return;
            }
        }

        // Preparar datos para guardar
        $data = [
            'codigo' => $postData['codigo'],
            'nombre' => $postData['nombre'],
            'ubicacion' => $postData['ubicacion'],
            'estado' => $postData['estado'],
            'fecha_inicio' => !empty($postData['fecha_inicio']) ? $postData['fecha_inicio'] : null,
            'plano_imagen' => $planoImagen,
            'observaciones' => $postData['observaciones'] ?? null
        ];

        // Guardar en base de datos
        $proyectoId = $this->proyectoModel->create($data);

        if ($proyectoId) {
            clearOldInput();
            $this->flash('success', 'Proyecto creado exitosamente');
            $this->redirect('/proyectos/show/' . $proyectoId);
        } else {
            $this->flash('error', 'Error al crear el proyecto');
            $this->redirect('/proyectos/create');
        }
    }

    /**
     * Muestra el detalle de un proyecto
     * GET /proyectos/show/{id}
     */
    public function show($id)
    {
        $this->requireAuth();

        $proyecto = $this->proyectoModel->findById($id);

        if (!$proyecto) {
            $this->flash('error', 'Proyecto no encontrado');
            $this->redirect('/proyectos');
            return;
        }

        // Obtener estadísticas del proyecto
        $estadisticas = $this->proyectoModel->getEstadisticas($id);

        $this->view('proyectos/show', [
            'title' => 'Detalle del Proyecto',
            'proyecto' => $proyecto,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Muestra el formulario para editar un proyecto
     * GET /proyectos/edit/{id}
     */
    public function edit($id)
    {
        $this->requireAuth();

        $proyecto = $this->proyectoModel->findById($id);

        if (!$proyecto) {
            $this->flash('error', 'Proyecto no encontrado');
            $this->redirect('/proyectos');
            return;
        }

        $this->view('proyectos/edit', [
            'title' => 'Editar Proyecto: ' . $proyecto['nombre'],
            'proyecto' => $proyecto,
            'estados' => PROYECTO_ESTADOS
        ]);
    }

    /**
     * Actualiza un proyecto existente
     * POST /proyectos/update/{id}
     */
    public function update($id)
    {
        $this->requireAuth();

        // Validar CSRF
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Token de seguridad inválido');
            $this->redirect('/proyectos/edit/' . $id);
            return;
        }

        // Verificar que el proyecto existe
        $proyecto = $this->proyectoModel->findById($id);
        if (!$proyecto) {
            $this->flash('error', 'Proyecto no encontrado');
            $this->redirect('/proyectos');
            return;
        }

        // Obtener datos del formulario
        $postData = $_POST;

        // Guardar datos para re-población en caso de error
        saveOldInput($postData);

        // Validaciones
        $errores = [];

        // Validar código (requerido, único excepto el actual)
        if (empty($postData['codigo'])) {
            $errores[] = 'El código del proyecto es obligatorio';
        } elseif ($postData['codigo'] !== $proyecto['codigo']) {
            if ($this->proyectoModel->codigoExists($postData['codigo'], $id)) {
                $errores[] = 'El código del proyecto ya existe';
            }
        }

        // Validar nombre (requerido)
        if (empty($postData['nombre'])) {
            $errores[] = 'El nombre del proyecto es obligatorio';
        }

        // Validar ubicación (requerida)
        if (empty($postData['ubicacion'])) {
            $errores[] = 'La ubicación es obligatoria';
        }

        // Validar estado
        if (empty($postData['estado']) || !array_key_exists($postData['estado'], PROYECTO_ESTADOS)) {
            $errores[] = 'Debe seleccionar un estado válido';
        }

        // Si hay errores, retornar
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $this->redirect('/proyectos/edit/' . $id);
            return;
        }

        // Manejar subida de nueva imagen del plano
        $planoImagen = $proyecto['plano_imagen']; // Mantener la imagen actual por defecto

        if (isset($_FILES['plano_imagen']) && $_FILES['plano_imagen']['error'] === UPLOAD_ERR_OK) {
            $nuevaImagen = $this->uploadImage($_FILES['plano_imagen'], 'planos');
            
            if ($nuevaImagen === false) {
                $errores[] = 'Error al subir la imagen del plano';
                $_SESSION['errores'] = $errores;
                $this->redirect('/proyectos/edit/' . $id);
                return;
            }

            // Eliminar imagen anterior si existe
            if (!empty($proyecto['plano_imagen'])) {
                $this->deleteImage($proyecto['plano_imagen']);
            }

            $planoImagen = $nuevaImagen;
        }

        // Preparar datos para actualizar
        $data = [
            'codigo' => $postData['codigo'],
            'nombre' => $postData['nombre'],
            'ubicacion' => $postData['ubicacion'],
            'estado' => $postData['estado'],
            'fecha_inicio' => !empty($postData['fecha_inicio']) ? $postData['fecha_inicio'] : null,
            'plano_imagen' => $planoImagen,
            'observaciones' => $postData['observaciones'] ?? null
        ];

        // Actualizar en base de datos
        $success = $this->proyectoModel->update($id, $data);

        if ($success) {
            clearOldInput();
            $this->flash('success', 'Proyecto actualizado exitosamente');
            $this->redirect('/proyectos/show/' . $id);
        } else {
            $this->flash('error', 'Error al actualizar el proyecto');
            $this->redirect('/proyectos/edit/' . $id);
        }
    }

    /**
     * Sube una imagen al servidor
     * 
     * @param array $file Archivo de $_FILES
     * @param string $folder Carpeta de destino
     * @return string|false Ruta relativa de la imagen o false si falla
     */
    private function uploadImage($file, $folder = 'uploads')
    {
        // Validar que es una imagen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        // Validar tamaño máximo (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        // Crear directorio si no existe
        $uploadDir = __DIR__ . '/../../uploads/' . $folder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'uploads/' . $folder . '/' . $filename;
        }

        return false;
    }

    /**
     * Elimina una imagen del servidor
     * 
     * @param string $path Ruta relativa de la imagen
     * @return bool
     */
    private function deleteImage($path)
    {
        $fullPath = __DIR__ . '/../../' . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
