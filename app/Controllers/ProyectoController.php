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

        // Agregar conteo de lotes a cada proyecto
        foreach ($proyectos as &$proyecto) {
            $proyecto['total_lotes'] = $this->proyectoModel->countLotes($proyecto['id']);
        }

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
        
        // RBAC: Solo administrador y consulta pueden crear proyectos
        $this->requireRole(['administrador', 'consulta']);

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
        
        // RBAC: Solo administrador y consulta pueden crear proyectos
        $this->requireRole(['administrador', 'consulta']);

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

        // Obtener todos los lotes del proyecto
        $loteModel = new \App\Models\LoteModel();
        $lotes = $loteModel->getByProyecto($id);

        // Calcular precio por m² para cada lote
        foreach ($lotes as &$lote) {
            $lote['precio_m2'] = $lote['area_m2'] > 0 
                ? round($lote['precio_lista'] / $lote['area_m2'], 0) 
                : 0;
        }

        $this->view('proyectos/show', [
            'title' => 'Detalle del Proyecto',
            'proyecto' => $proyecto,
            'estadisticas' => $estadisticas,
            'lotes' => $lotes
        ]);
    }

    /**
     * Muestra el formulario para editar un proyecto
     * GET /proyectos/edit/{id}
     */
    public function edit($id)
    {
        $this->requireAuth();
        
        // RBAC: Solo administrador, consulta y vendedor pueden editar proyectos
        $this->requireRole(['administrador', 'consulta', 'vendedor']);

        $proyecto = $this->proyectoModel->findById($id);

        if (!$proyecto) {
            $this->flash('error', 'Proyecto no encontrado');
            $this->redirect('/proyectos');
            return;
        }

        // Agregar conteo de lotes
        $proyecto['total_lotes'] = $this->proyectoModel->countLotes($id);

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
        
        // RBAC: Solo administrador y consulta pueden actualizar proyectos
        $this->requireRole(['administrador', 'consulta']);

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

        try {
            if (isset($_FILES['plano_imagen']) && $_FILES['plano_imagen']['error'] === UPLOAD_ERR_OK) {
                $nuevaImagen = $this->uploadImage($_FILES['plano_imagen'], 'planos');
                
                if ($nuevaImagen === false) {
                    $errores[] = 'Error al subir la imagen del plano. Verifique el formato y tamaño.';
                    $_SESSION['errores'] = $errores;
                    $this->redirect('/proyectos/edit/' . $id);
                    return;
                }

                // Eliminar imagen anterior si existe
                if (!empty($proyecto['plano_imagen'])) {
                    $this->deleteImage($proyecto['plano_imagen']);
                }

                $planoImagen = $nuevaImagen;
            } elseif (isset($_FILES['plano_imagen']) && $_FILES['plano_imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Hubo un error en la subida
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede upload_max_filesize en php.ini',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede MAX_FILE_SIZE del formulario',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta directorio temporal',
                    UPLOAD_ERR_CANT_WRITE => 'No se puede escribir en el disco',
                    UPLOAD_ERR_EXTENSION => 'Extensión de PHP detuvo la subida'
                ];
                
                $errorMsg = $uploadErrors[$_FILES['plano_imagen']['error']] ?? 'Error desconocido al subir archivo';
                $errores[] = $errorMsg;
                $_SESSION['errores'] = $errores;
                $this->redirect('/proyectos/edit/' . $id);
                return;
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
                $this->flash('error', 'Error al actualizar el proyecto en la base de datos');
                $this->redirect('/proyectos/edit/' . $id);
            }
        } catch (\Exception $e) {
            // Capturar cualquier excepción y mostrar mensaje detallado
            \Logger::error('Error en ProyectoController::update - ' . $e->getMessage());
            $this->flash('error', 'Error al actualizar proyecto: ' . $e->getMessage());
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
        try {
            // Validar que es una imagen
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                \Logger::error("Tipo de archivo no permitido: {$file['type']}");
                return false;
            }

            // Validar tamaño máximo (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                \Logger::error("Archivo muy grande: {$file['size']} bytes");
                return false;
            }

            // Crear directorio si no existe
            $uploadDir = __DIR__ . '/../../uploads/' . $folder;
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    \Logger::error("No se pudo crear directorio: {$uploadDir}");
                    return false;
                }
            }

            // Validar que el directorio es escribible
            if (!is_writable($uploadDir)) {
                \Logger::error("Directorio no escribible: {$uploadDir}");
                return false;
            }

            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . '/' . $filename;

            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                \Logger::info("Imagen subida exitosamente: uploads/{$folder}/{$filename}");
                return 'uploads/' . $folder . '/' . $filename;
            } else {
                \Logger::error("Error al mover archivo desde {$file['tmp_name']} a {$filepath}");
                return false;
            }
        } catch (\Exception $e) {
            \Logger::error("Excepción en uploadImage: " . $e->getMessage());
            return false;
        }
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

    /**
     * Elimina un proyecto
     * POST /proyectos/delete/{id}
     */
    public function delete($id)
    {
        $this->requireAuth();
        
        // RBAC: Solo administrador puede eliminar proyectos
        $user = user();
        if ($user['rol'] === 'consulta') {
            setFlash('error', 'El rol consulta no tiene permisos para eliminar proyectos');
            redirect('/proyectos');
            return;
        }
        
        if ($user['rol'] === 'vendedor') {
            setFlash('error', 'El rol vendedor no tiene permisos para eliminar proyectos');
            redirect('/proyectos');
            return;
        }

        // Verificar permisos adicionales
        if (!can('eliminar_proyectos')) {
            setFlash('error', 'No tienes permisos para eliminar proyectos');
            redirect('/proyectos');
            return;
        }

        // Validar CSRF
        if (!$this->validateCsrf()) {
            setFlash('error', 'Token de seguridad inválido');
            redirect('/proyectos');
        }

        // Verificar si el proyecto existe
        $proyecto = $this->proyectoModel->findById($id);
        if (!$proyecto) {
            setFlash('error', 'Proyecto no encontrado');
            redirect('/proyectos');
        }

        // Verificar si tiene lotes asociados
        $totalLotes = $this->proyectoModel->countLotes($id);
        if ($totalLotes > 0) {
            setFlash('error', "No se puede eliminar el proyecto porque tiene {$totalLotes} lote(s) asociado(s). Elimina los lotes primero.");
            redirect('/proyectos');
        }

        // Eliminar imagen si existe
        if (!empty($proyecto['plano_imagen'])) {
            $this->deleteImage($proyecto['plano_imagen']);
        }

        // Eliminar proyecto
        if ($this->proyectoModel->delete($id)) {
            setFlash('success', 'Proyecto eliminado correctamente');
        } else {
            setFlash('error', 'Error al eliminar el proyecto');
        }

        redirect('/proyectos');
    }

    /**
     * Actualiza las coordenadas de los lotes en el plano
     * POST /proyectos/update-coordenadas/{id}
     */
    public function updateCoordenadas($id)
    {
        $this->requireAuth();
        $this->requireRole(['administrador', 'consulta', 'vendedor']);

        // Obtener datos JSON primero (para poder acceder al token en el body si está ahí)
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Si el token viene en el body JSON, agregarlo a $_POST temporalmente para validación
        if (isset($data['csrf_token']) && !empty($data['csrf_token'])) {
            $_POST['csrf_token'] = $data['csrf_token'];
        }

        // Validar CSRF
        if (!$this->validateCsrf()) {
            http_response_code(403);
            echo json_encode([
                'success' => false, 
                'message' => 'Token CSRF inválido',
                'debug' => [
                    'post_token' => $_POST['csrf_token'] ?? 'no presente',
                    'header_token' => $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 'no presente',
                    'session_exists' => isset($_SESSION['csrf_token']) ? 'sí' : 'no'
                ]
            ]);
            return;
        }

        if (!isset($data['lotes']) || !is_array($data['lotes'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }

        // Actualizar coordenadas de cada lote
        $loteModel = new \App\Models\LoteModel();
        $actualizados = 0;

        foreach ($data['lotes'] as $lote) {
            if (isset($lote['id'], $lote['x'], $lote['y'])) {
                if ($loteModel->updateCoordenadas($lote['id'], $lote['x'], $lote['y'])) {
                    $actualizados++;
                }
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Se actualizaron {$actualizados} lotes",
            'actualizados' => $actualizados
        ]);
    }

    /**
     * Obtiene los lotes con coordenadas en formato JSON
     * GET /proyectos/lotes-coordenadas/{id}
     */
    public function getLotesCoordenadas($id)
    {
        $this->requireAuth();

        $loteModel = new \App\Models\LoteModel();
        $lotes = $loteModel->getLotesConCoordenadas($id);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'lotes' => $lotes]);
    }
}
