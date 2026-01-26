<?php
declare(strict_types=1);

namespace Wappointment\Routes;

use Wappointment\Actions\Routes\GetRoutesForJsAction;
use Wappointment\Actions\Routes\RegisterRestRoutesAction;
use Wappointment\Actions\Routes\SetCacheHeadersAction;

/**
 * REST API routes configuration
 */
class RestApi
{
    private string $namespace = 'wappointment/v1';
    private array $routes = [];

    public function __construct(
        private GetRoutesForJsAction $getRoutesForJsAction,
        private RegisterRestRoutesAction $registerRestRoutesAction,
        private SetCacheHeadersAction $setCacheHeadersAction
    ) {
        // Load routes from configuration file
        $this->loadRoutes();
        
        // Add filter to handle cache headers
        add_filter('rest_post_dispatch', [$this, 'setCacheHeaders'], 10, 3);
    }

    /**
     * Load routes from configuration file
     */
    private function loadRoutes(): void
    {
        $routesConfig = require __DIR__ . '/api.php';
        
        foreach ($routesConfig as $route) {
            $method = $route['method'] ?? 'GET';
            $path = $route['path'];
            
            // Convert DELETE, PUT, PATCH to POST with path suffix
            if (in_array(strtoupper($method), ['DELETE', 'PUT', 'PATCH'])) {
                $suffix = '/' . strtolower($method);
                // Only add suffix if it's not already there
                if (!str_ends_with($path, $suffix)) {
                    $path = $path . $suffix;
                }
                $method = 'POST';
            }
            
            $this->routes[] = [
                'name' => $route['name'] ?? null,
                'method' => $method,
                'path' => $path,
                'controller' => $route['controller'],
                'cacheable' => $route['cacheable'] ?? true,
                'permission_callback' => $route['permission_callback'] ?? function() {
                    return current_user_can('manage_options');
                },
                'original_method' => $route['method'] ?? 'GET',
                'original_path' => $route['path'],
            ];
        }
    }

    /**
     * Get all routes for JavaScript
     */
    public function getRoutesForJs(): array
    {
        return $this->getRoutesForJsAction->handle($this->routes);
    }

    /**
     * Register all REST API routes
     */
    public function register(): void
    {
        $this->registerRestRoutesAction->handle($this->namespace, $this->routes);
    }

    /**
     * Set cache headers based on route configuration
     */
    public function setCacheHeaders(\WP_REST_Response $response, \WP_REST_Server $server, \WP_REST_Request $request): \WP_REST_Response
    {
        return $this->setCacheHeadersAction->handle($response, $request, $this->namespace, $this->routes);
    }
}
