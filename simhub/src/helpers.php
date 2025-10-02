<?php
// src/helpers.php

/**
 * Returns the base path of the application considering the current script.
 */
function base_path(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($script));
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        return '';
    }
    return rtrim($dir, '/');
}

/**
 * Build an absolute-ish path anchored to the application base.
 */
function asset(string $path = ''): string {
    $base = base_path();
    $trimmed = ltrim($path, '/');
    if ($trimmed === '') {
        return $base ?: '/';
    }
    $prefix = $base ? $base . '/' : '/';
    return $prefix . $trimmed;
}

/**
 * Redirect helper that keeps deployments working from sub-directories.
 */
function redirect_to(string $path): void {
    header('Location: ' . asset($path));
    exit;
}

function app_config(): array {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function absolute_url(string $path = ''): string {
    $cfg = app_config();
    $base = rtrim($cfg['app_url'] ?? '', '/');
    $relative = asset($path);
    if ($relative === '' || $relative === '/') {
        return $base ?: $relative;
    }
    if (strpos($relative, 'http://') === 0 || strpos($relative, 'https://') === 0) {
        return $relative;
    }
    return ($base ?: '') . $relative;
}
