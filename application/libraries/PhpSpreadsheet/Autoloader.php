<?php
namespace PhpOffice\PhpSpreadsheet;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $baseDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

            $prefix = 'PhpOffice\\PhpSpreadsheet\\';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}
