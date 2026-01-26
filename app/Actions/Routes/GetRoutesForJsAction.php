<?php
declare(strict_types=1);

namespace Wappointment\Actions\Routes;

/**
 * Get all routes formatted for JavaScript
 */
class GetRoutesForJsAction
{
    public function handle(array $routes): array
    {
        $jsRoutes = [];
        foreach ($routes as $route) {
            if (isset($route['name'])) {
                $jsRoutes[$route['name']] = [
                    'method' => $route['method'],
                    'path' => $route['path'],
                ];
            }
        }
        return $jsRoutes;
    }
}
