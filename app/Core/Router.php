<?php
namespace App\Core;

use App\Core\Logger;

class Router
{
    private $routes = [];

    public function get(string $path, $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->normalizePath($path),
            'handler' => $handler,
        ];
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route['path']);
            $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $handler = $route['handler'];

                if (is_array($handler)) {
                    $controller = $handler[0];
                    $action = $handler[1];
                    call_user_func_array([new $controller(), $action], $matches);
                    return;
                }

                call_user_func_array($handler, $matches);
                return;
            }
        }

        Logger::write('warning', 'route_not_found', ['method' => $method, 'uri' => $uri]);
        http_response_code(404);
        include __DIR__ . '/../Views/errors/404.php';
    }
}
