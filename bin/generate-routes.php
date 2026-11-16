<?php
declare(strict_types=1);

/**
 * Regenerate resources/js-react/config/routes.js from app/Routes/api.php.
 *
 * Usage: php bin/generate-routes.php [--check]
 *   --check  exit 1 if the committed file is out of date (for CI), without writing.
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$root = dirname(__DIR__);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'Wappointment\\';
    if (str_starts_with($class, $prefix)) {
        $file = $root . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

$output = $root . '/resources/js-react/config/routes.js';
$generator = new Wappointment\System\RoutesGenerator();

if (in_array('--check', $argv, true)) {
    $current = is_file($output) ? file_get_contents($output) : '';
    if ($current !== $generator->render()) {
        fwrite(STDERR, "routes.js is out of date. Run: php bin/generate-routes.php\n");
        exit(1);
    }
    echo "routes.js is up to date.\n";
    exit(0);
}

$generator->generate($output);
echo "Generated {$output}\n";
