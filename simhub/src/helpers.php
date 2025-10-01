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
