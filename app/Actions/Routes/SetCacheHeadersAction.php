<?php
declare(strict_types=1);

namespace Wappointment\Actions\Routes;

/**
 * Set cache headers for REST API responses
 */
class SetCacheHeadersAction
{
    public function handle(
        \WP_REST_Response $response,
        \WP_REST_Request $request,
        string $namespace,
        array $routes
    ): \WP_REST_Response {
        $route = $request->get_route();
        
        // Check if route is in our namespace
        if (strpos($route, '/' . $namespace . '/') !== 0) {
            return $response;
        }

        // Find matching route configuration
        $routePath = str_replace('/' . $namespace, '', $route);
        
        foreach ($routes as $routeConfig) {
            if ($routeConfig['path'] === $routePath) {
                if ($routeConfig['cacheable']) {
                    $response->header('Cache-Control', 'public, max-age=3600');
                } else {
                    $response->header('Cache-Control', 'no-cache, must-revalidate, max-age=0');
                }
                break;
            }
        }

        return $response;
    }
}
