<?php

/**
 * Router - Sistema de enrutamiento dinámico
 * Maneja el mapeo de URLs amigables a controladores y métodos
 */
class Router
{
    private $routes = [];
    private $baseUrl;

    public function __construct($baseUrl = '')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Registra una ruta GET
     */
    public function get($uri, $handler)
    {
        $this->addRoute('GET', $uri, $handler);
    }

    /**
     * Registra una ruta POST
     */
    public function post($uri, $handler)
    {
        $this->addRoute('POST', $uri, $handler);
    }

    /**
     * Añade una ruta al sistema
     */
    private function addRoute($method, $uri, $handler)
    {
        $uri = '/' . trim($uri, '/');
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
            'pattern' => $this->convertUriToPattern($uri)
        ];
    }

    /**
     * Convierte una URI con parámetros en un patrón regex
     * Ej: /proyecto/{id} -> /proyecto/([0-9]+)
     */
    private function convertUriToPattern($uri)
    {
        $pattern = preg_replace('/\{([a-z_]+)\}/', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Despacha la ruta actual
     */
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $this->getRequestUri();
        
        error_log("DEBUG ROUTER: Method={$requestMethod}, URI={$requestUri}");

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match($route['pattern'], $requestUri, $matches)) {
                array_shift($matches); // Eliminar la coincidencia completa
                return $this->callHandler($route['handler'], $matches);
            }
        }

        // No se encontró la ruta
        $this->handleNotFound();
    }

    /**
     * Obtiene la URI de la solicitud actual
     */
    private function getRequestUri()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover el subdirectorio si existe
        if ($this->baseUrl !== '') {
            $uri = str_replace($this->baseUrl, '', $uri);
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Llama al handler de la ruta
     */
    private function callHandler($handler, $params = [])
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        // Formato: ControllerName@methodName
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            
            $controllerClass = "App\\Controllers\\{$controller}";
            $controllerFile = __DIR__ . "/../app/Controllers/{$controller}.php";

            if (!file_exists($controllerFile)) {
                throw new Exception("Controller file not found: {$controllerFile}");
            }

            require_once $controllerFile;

            if (!class_exists($controllerClass)) {
                throw new Exception("Controller class not found: {$controllerClass}");
            }

            $controllerInstance = new $controllerClass();

            if (!method_exists($controllerInstance, $method)) {
                throw new Exception("Method {$method} not found in {$controllerClass}");
            }

            return call_user_func_array([$controllerInstance, $method], $params);
        }

        throw new Exception("Invalid route handler");
    }

    /**
     * Maneja rutas no encontradas
     */
    private function handleNotFound()
    {
        http_response_code(404);
        echo "404 - Página no encontrada";
        exit;
    }

    /**
     * Genera una URL para una ruta nombrada
     */
    public function url($path)
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }
}
