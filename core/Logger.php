<?php

/**
 * Logger - Sistema de logging personalizado
 */
class Logger
{
    private static $logFile = null;
    private static $logToFile = true;
    private static $logToPhp = true;

    /**
     * Inicializa el logger
     */
    public static function init()
    {
        if (self::$logFile === null) {
            $logDir = dirname(__DIR__) . '/storage/logs';
            
            // Crear directorio si no existe
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            self::$logFile = $logDir . '/app.log';
        }
    }

    /**
     * Escribe un log de información
     */
    public static function info($message, $context = [])
    {
        self::write('INFO', $message, $context);
    }

    /**
     * Escribe un log de error
     */
    public static function error($message, $context = [])
    {
        self::write('ERROR', $message, $context);
    }

    /**
     * Escribe un log de advertencia
     */
    public static function warning($message, $context = [])
    {
        self::write('WARNING', $message, $context);
    }

    /**
     * Escribe un log de debug
     */
    public static function debug($message, $context = [])
    {
        self::write('DEBUG', $message, $context);
    }

    /**
     * Escribe el log en archivo y/o error_log de PHP
     */
    private static function write($level, $message, $context = [])
    {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}";
        
        // Escribir en archivo personalizado
        if (self::$logToFile) {
            file_put_contents(
                self::$logFile,
                $logMessage . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        }
        
        // Escribir también en error_log de PHP
        if (self::$logToPhp) {
            error_log($logMessage);
        }
    }

    /**
     * Obtiene las últimas líneas del log
     */
    public static function tail($lines = 50)
    {
        self::init();
        
        if (!file_exists(self::$logFile)) {
            return [];
        }
        
        $file = file(self::$logFile);
        return array_slice($file, -$lines);
    }

    /**
     * Limpia el archivo de log
     */
    public static function clear()
    {
        self::init();
        
        if (file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, '');
        }
    }

    /**
     * Obtiene la ruta del archivo de log
     */
    public static function getLogFile()
    {
        self::init();
        return self::$logFile;
    }
}
