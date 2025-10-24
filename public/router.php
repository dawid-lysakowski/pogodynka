<?php

// Router for the PHP built-in web server.
// If the requested resource exists as a file, return false so the web server serves it directly.
// Otherwise, forward the request to the Symfony front controller (public/index.php).
if (php_sapi_name() === 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . ($url['path'] ?? '/');

    if ($file !== false && is_file($file)) {
        return false;
    }
}

require __DIR__ . '/index.php';
