<?php
namespace App\Core;

class Router {
    private $routes = [];

    public function get($route, $action) {
        $this->routes['GET'][$route] = $action;
    }

    public function post($route, $action) {
        $this->routes['POST'][$route] = $action;
    }

    public function dispatch($uri, $method) {
        $url = parse_url($uri, PHP_URL_PATH);
        
        // Soporte si el proyecto corre desde una subcarpeta en xampp (htdocs)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            // Normalizar slashes
            $scriptName = str_replace('\\', '/', $scriptName);
            $url = str_replace($scriptName, '', $url);
        }
        if (empty($url) || $url === '') $url = '/';

        if (isset($this->routes[$method][$url])) {
            $action = $this->routes[$method][$url];
            
            if (is_callable($action)) {
                return $action();
            }

            list($controllerName, $methodName) = explode('@', $action);
            $controllerClass = "App\\Controllers\\$controllerName";
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    return $controller->$methodName();
                }
            }
            http_response_code(500);
            echo "Error: Método $methodName no encontrado en $controllerClass";
            return;
        }

        http_response_code(404);
        echo "404 - Not Found ($method $url)";
    }
}
