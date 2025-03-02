<?php
namespace App;

class PatternRouter
{
    private function stripParameters($uri)
    {
        if (str_contains($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        return $uri;
    }

    public function route($uri)
    {
        $api = false;
        if (str_starts_with($uri, "api/")) {
            $uri = substr($uri, 4);
            $api = true;
        }

        $defaultcontroller = 'home';
        $defaultmethod = 'index';

        $uri = $this->stripParameters($uri);

        $explodedUri = explode('/', $uri);

        if (!isset($explodedUri[0]) || empty($explodedUri[0])) {
            $explodedUri[0] = $defaultcontroller;
        }
        $controllerName = ucfirst(strtolower($explodedUri[0])) . "Controller";

        if (!isset($explodedUri[1]) || empty($explodedUri[1])) {
            $explodedUri[1] = $defaultmethod;
        }
        $methodName = $explodedUri[1];

        $filename = __DIR__ . '/controllers/' . $controllerName . '.php';
        if ($api) {
            $filename = __DIR__ . '/api/controllers/' . $controllerName . '.php';
        }
        if (file_exists($filename)) {
            require $filename;
        } else {
            http_response_code(404);
            die();
        }

        $controllerClass = $api ? "App\\Api\\Controllers\\{$controllerName}" : "App\\Controllers\\{$controllerName}";

        if (class_exists($controllerClass) && method_exists($controllerClass, $methodName)) {
            try {
                $controllerObj = new $controllerClass();
                $controllerObj->{$methodName}();
            } catch (Error $e) {
                http_response_code(500);
                die();
            }
        } else {
            http_response_code(404);
            die();
        }
    }
}
?>