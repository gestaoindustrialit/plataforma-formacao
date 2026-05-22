<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, callable|array $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = rtrim($uri, '/') ?: '/';
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route['path']);
            $pattern = '#^' . rtrim($pattern, '/') . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$controller, $action] = $handler;
                    (new $controller())->$action(...$matches);
                    return;
                }
                $handler(...$matches);
                return;
            }
        }

        http_response_code(404);
        include __DIR__ . '/../Views/errors/404.php';
    }
}
