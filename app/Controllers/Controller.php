<?php

namespace App\Controllers;

/**
 * Controller - Clase base para todos los controladores
 * Proporciona funcionalidades comunes como renderizado de vistas,
 * redirección, flash messages, etc.
 */
abstract class Controller
{
    /**
     * Renderiza una vista con layout
     */
    protected function view($view, $data = [])
    {
        extract($data);
        
        ob_start();
        require_once __DIR__ . "/../Views/{$view}.php";
        $content = ob_get_clean();
        
        require_once __DIR__ . "/../Views/layouts/app.php";
    }

    /**
     * Renderiza una vista sin layout (para modals, partials, etc.)
     */
    protected function partial($view, $data = [])
    {
        extract($data);
        require_once __DIR__ . "/../Views/{$view}.php";
    }

    /**
     * Redirección
     */
    protected function redirect($url)
    {
        header("Location: " . url($url));
        exit;
    }

    /**
     * Redirección con mensaje flash
     */
    protected function redirectWithFlash($url, $type, $message)
    {
        $this->flash($type, $message);
        $this->redirect($url);
    }

    /**
     * Establece un mensaje flash
     */
    protected function flash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Retorna respuesta JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Valida que el usuario esté autenticado
     */
    protected function requireAuth()
    {
        requireAuth();
    }

    /**
     * Obtiene input POST
     */
    protected function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtiene input GET
     */
    protected function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Valida si es una petición POST
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Valida si es una petición GET
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}
