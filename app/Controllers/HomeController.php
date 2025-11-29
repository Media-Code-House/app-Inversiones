<?php

namespace App\Controllers;

/**
 * HomeController - Controlador principal
 * Maneja el dashboard y páginas generales
 */
class HomeController
{
    /**
     * Dashboard principal (placeholder para Módulo 3)
     */
    public function dashboard()
    {
        requireAuth();
        
        $this->render('home/dashboard', [
            'title' => 'Dashboard - ' . APP_NAME
        ]);
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
