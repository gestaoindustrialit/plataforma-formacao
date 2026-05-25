<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function base_path_url(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    return $base === '/' ? '' : $base;
}

function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return base_path_url() . ($path === '/' ? '' : $path);
}
