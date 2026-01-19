<?php
declare(strict_types=1);

namespace Wappointment\Routes;

/**
 * REST API routes configuration
 */
class RestApi
{
    private string $namespace = 'wappointment/v1';
    private array $routes = [];

    public function __construct()
    {
        // Auto-discover route classes
        $this->discoverRoutes();
        
        // Add filter to handle cache headers
        add_filter('rest_post_dispatch', [$this, 'setCacheHeaders'], 10, 3);
    }

    /**
     * Discover all route classes in Api directory
     */
    private function discoverRoutes(): void
    {
        $routesDir = __DIR__ . '/Api';
        
        if (!is_dir($routesDir)) {
            return;
        }

        $files = glob($routesDir . '/*Route.php');
        
        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClassName = "Wappointment\\Routes\\Api\\{$className}";
            
            if (class_exists($fullClassName)) {
                $this->routes[] = new $fullClassName();
            }
        }
    }

    /**
     * Register all discovered REST API routes
     */
    public function register(): void
    {
        foreach ($this->routes as $route) {
            $route->register();
        }
    }

    /**
     * Set cache headers based on route configuration
     */
    public function setCacheHeaders(\WP_REST_Response $response, \WP_REST_Server $server, \WP_REST_Request $request): \WP_REST_Response
    {
        $route = $request->get_route();
        
        // Check if route is in our namespace
        if (strpos($route, '/' . $this->namespace . '/') !== 0) {
            return $response;
        }

        // Get route handler
        $routes = $server->get_routes();
        $route_config = $routes[$route] ?? null;
        
        if (!$route_config) {
            return $response;
        }

        // Check cacheable flag from route args
        $cacheable = false;
        foreach ($route_config as $handler) {
            if (isset($handler['args']['cacheable'])) {
                $cacheable = $handler['args']['cacheable'];
                break;
            }
        }

        // Set appropriate cache headers
        if ($cacheable) {
            // Cacheable: 1 hour
            $response->header('Cache-Control', 'public, max-age=3600');
            $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        } else {
            // Not cacheable
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        }

        return $response;
    }
}
