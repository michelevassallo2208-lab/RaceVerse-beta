<?php
// src/helpers.php
function asset(string $path): string {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if ($base === '/' || $base === '\\') $base = '';
    return $base . '/' . ltrim($path, '/');
}
