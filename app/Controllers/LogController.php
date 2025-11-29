<?php

namespace App\Controllers;

/**
 * LogController - Visor de logs de la aplicación
 */
class LogController extends Controller
{
    public function __construct()
    {
        // Solo administradores pueden ver logs
        if (!isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a los logs';
            redirect('/');
            exit;
        }
    }

    /**
     * Muestra el visor de logs
     * GET /logs
     */
    public function index()
    {
        $logFile = \Logger::getLogFile();
        $logs = [];
        
        if (file_exists($logFile)) {
            $logs = \Logger::tail(100); // Últimas 100 líneas
        }
        
        $data = [
            'pageTitle' => 'Logs del Sistema',
            'logs' => $logs,
            'logFile' => $logFile
        ];
        
        $this->view('system/logs', $data);
    }

    /**
     * Obtiene logs en formato JSON (para AJAX)
     * GET /logs/fetch
     */
    public function fetch()
    {
        header('Content-Type: application/json');
        
        $lines = $_GET['lines'] ?? 50;
        $logs = \Logger::tail($lines);
        
        echo json_encode([
            'success' => true,
            'logs' => $logs,
            'count' => count($logs)
        ]);
        exit;
    }

    /**
     * Limpia el archivo de logs
     * POST /logs/clear
     */
    public function clear()
    {
        if (!$this->validateCsrf()) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('/logs');
            return;
        }
        
        \Logger::clear();
        \Logger::info("Logs limpiados por usuario: " . ($_SESSION['user']['nombre'] ?? 'Desconocido'));
        
        $_SESSION['success'] = 'Logs limpiados exitosamente';
        redirect('/logs');
    }

    /**
     * Descarga el archivo de logs
     * GET /logs/download
     */
    public function download()
    {
        $logFile = \Logger::getLogFile();
        
        if (!file_exists($logFile)) {
            $_SESSION['error'] = 'Archivo de logs no encontrado';
            redirect('/logs');
            return;
        }
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="app-' . date('Y-m-d-His') . '.log"');
        header('Content-Length: ' . filesize($logFile));
        readfile($logFile);
        exit;
    }
}
