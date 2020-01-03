<?php
/**
 * autoload.php
 */
spl_autoload_register(
    function ($class) {
        $prefix = 'PHPMailer\\PHPMailer\\';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relative_class = substr($class, $len);
        $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
);
