<?php

namespace App\Core;

use App\Controller\ErrorController;

class Router
{
    private array $routes = [];

    public function __construct(string $routesFile = 'routes.yml')
    {
        $routesPath = __DIR__ . '/../' . $routesFile;
 
        if (!file_exists($routesPath)) {
            throw new \Exception("Le fichier de routes '{$routesFile}' est introuvable dans le dossier '{$routesPath}'");
        }

        if (!function_exists('yaml_parse_file')) {
            throw new \Exception("L'extension PHP YAML n'est pas installée. Veuillez l'activer.");
        }

        $this->routes = yaml_parse_file($routesPath);
        if ($this->routes === false) {
            throw new \Exception("Erreur lors de l'analyse du fichier de routes YAML.");
        }
    }

    public function dispatch(): void
    {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $uri = rtrim($uri, '/');
        $uri = $uri === '' ? '/' : $uri;

        if (!isset($this->routes[$uri])) {
            $slug = ltrim($uri, '/');
            $pageManager = new \App\Service\PageManager();
            $page = $pageManager->findBySlug($slug);

            if ($page) {
                $controller = new \App\Controller\PageController();
                $controller->view($slug);
                return;
            }

            $this->handleError(404, "Route non trouvée pour l'URI: {$uri}");
            return;
        }

        $route = $this->routes[$uri];
        $controllerName = 'App\\Controller\\' . $route['controller'];
        $actionName = $route['action'];

        $controller = new $controllerName();

        if (!method_exists($controller, $actionName)) {
            $this->handleError(500, "Action '{$actionName}' introuvable dans le contrôleur '{$controllerName}'.");
            return;
        }

        $uriParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $idFromPath = $uriParts[1] ?? null;
        $idFromQuery = $_GET['id'] ?? null;
        $id = $idFromPath ?? $idFromQuery;

        if ($id !== null) {
            $controller->$actionName((int)$id);
        } else {
            $controller->$actionName();
        }
    }

    private function handleError(int $code, string $message): void
    {
        http_response_code($code);
        $errorController = new ErrorController();
        $errorController->show($code, $message);
    }
}
