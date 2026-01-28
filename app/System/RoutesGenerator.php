<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Routes\RestApi;

/**
 * Generate JavaScript routes file from PHP route definitions
 */
class RoutesGenerator
{
    private string $outputPath;

    public function __construct()
    {
        $this->outputPath = WAPPOINTMENT_PATH . 'resources/js-react/config/routes.js';
    }

    /**
     * Generate the JavaScript routes file
     */
    public function generate(): void
    {
        $restApi = new RestApi();
        $routes = $restApi->getRoutesForJs();

        $js = "// Auto-generated file - DO NOT EDIT MANUALLY\n";
        $js .= "// Generated from app/Routes/api.php\n\n";
        $js .= "export const routes = " . $this->toJavaScript($routes) . ";\n\n";
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

        // Ensure directory exists
        $dir = dirname($this->outputPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->outputPath, $js);
    }

    /**
     * Convert PHP array to JavaScript object notation
     */
    private function toJavaScript(array $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return $json;
    }
}
