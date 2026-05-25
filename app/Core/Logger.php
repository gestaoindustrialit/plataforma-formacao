<?php

namespace App\Core;

class Logger
{
    public static function write(string $level, string $message, array $context = []): void
    {
        $config = require __DIR__ . '/../../config/config.php';
        $logDir = $config['storage_path'] . '/logs';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? json_encode($context) : ''
        );

        @file_put_contents($logDir . '/app.log', $line, FILE_APPEND);
    }
}
