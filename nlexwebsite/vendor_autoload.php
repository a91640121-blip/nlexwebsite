<?php
// Very small autoloader for App\Classes namespace inside classes/
spl_autoload_register(function ($class) {
    $prefix = 'App\\Classes\\';
    $base_dir = __DIR__ . '/classes/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
