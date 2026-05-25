<?php

declare(strict_types=1);

session_name('cfo_session');
session_start();

require_once __DIR__ . '/../app/Core/Helpers.php';
require_once __DIR__ . '/../app/Core/Logger.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strpos($class, $prefix) === 0) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

$config = require __DIR__ . '/../config/config.php';

set_error_handler(function ($severity, $message, $file, $line) {
    App\Core\Logger::write('error', $message, ['file' => $file, 'line' => $line, 'severity' => $severity]);
    return false;
});

set_exception_handler(function ($exception) {
    App\Core\Logger::write('critical', $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ]);
    http_response_code(500);
    echo 'Erro interno da aplicação. Verifique storage/logs/app.log';
});

$router = new App\Core\Router();
require __DIR__ . '/../routes/web.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($basePath && strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}
$uri = $requestPath ?: '/';

App\Core\Logger::write('info', 'dispatch', ['method' => $_SERVER['REQUEST_METHOD'], 'uri' => $uri]);
$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
