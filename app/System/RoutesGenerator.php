<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Actions\Routes\GetRoutesForJsAction;
use Wappointment\Routes\RouteTable;

/**
 * Build-time generator of resources/js-react/config/routes.js from app/Routes/api.php.
 *
 * Runs from bin/generate-routes.php (npm run routes). It is never executed by the
 * plugin at runtime: the file is bundled by webpack, and plugin folders may be read-only.
 */
class RoutesGenerator
{
    public function __construct(private GetRoutesForJsAction $getRoutesForJs = new GetRoutesForJsAction()) {}

    public function render(): string
    {
        $routes = $this->getRoutesForJs->handle(RouteTable::load());

        $js = "// Auto-generated file - DO NOT EDIT MANUALLY\n";
        $js .= "// Generated from app/Routes/api.php\n";
        $js .= "// Run: php bin/generate-routes.php to regenerate\n\n";
        $js .= "export const routes = " . json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . ";\n\n";
        $js .= "export function buildRoute(name, params = {}) {\n";
        $js .= "    const route = routes[name];\n";
        $js .= "    if (!route) {\n";
        $js .= "        throw new Error(`Route \${name} not found`);\n";
        $js .= "    }\n\n";
        $js .= "    let path = route.path;\n";
        $js .= "    // Replace WordPress regex patterns with actual values\n";
        $js .= "    Object.keys(params).forEach(key => {\n";
        $js .= "        path = path.replace(new RegExp(`\\\\(\\\\?P<\${key}>.*?\\\\)`), params[key]);\n";
        $js .= "    });\n\n";
        $js .= "    return { path, method: route.method };\n";
        $js .= "}\n";
        return $js;
    }

    public function generate(string $outputPath): void
    {
        $dir = dirname($outputPath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create {$dir}.");
        }
        if (file_put_contents($outputPath, $this->render()) === false) {
            throw new \RuntimeException("Cannot write {$outputPath}.");
        }
    }
}
