<?php
declare(strict_types=1);

namespace Wappointment\Routes;

use Wappointment\System\Container;

/**
 * REST API routes configuration
 */
class RestApi
{
    private string $namespace = 'wappointment/v1';
    private array $routes = [];

    public function __construct()
    {
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
            $this->routes[] = [
                'method' => $route['method'] ?? 'GET',
                'path' => $route['path'],
                'controller' => $route['controller'],
                'cacheable' => $route['cacheable'] ?? true,
                'permission_callback' => $route['permission_callback'] ?? function() {
                    return current_user_can('manage_options');
                }
            ];
        }
    }

    /**
     * Register all REST API routes
     */
    public function register(): void
    {
        foreach ($this->routes as $route) {
            register_rest_route(
                $this->namespace,
                $route['path'],
                [
                    'methods' => $route['method'],
                    'callback' => function(\WP_REST_Request $request) use ($route) {
                        $container = Container::getInstance();
                        $controller = $container->make($route['controller']);
                        
                        // Get method parameters
                        $reflection = new \ReflectionMethod($controller, '__invoke');
                        $params = $reflection->getParameters();
                        
                        $args = [];
                        foreach ($params as $param) {
                            $type = $param->getType();
                            if ($type && !$type->isBuiltin()) {
                                $className = $type->getName();
                                
                                try {
                                    // Check if class has a static 'fromWpRequest' method
                                    if (method_exists($className, 'fromWpRequest')) {
                                        $args[] = $className::fromWpRequest($request);
                                    } elseif (method_exists($className, 'from')) {
                                        $args[] = $className::from($request);
                                    } else {
                                        // Resolve from container with request parameter
                                        $args[] = $container->make($className, ['request' => $request]);
                                    }
                                } catch (\InvalidArgumentException $e) {
                                    // Return validation error response
                                    return new \WP_REST_Response(['error' => $e->getMessage()], 400);
                                }
                            }
                        }
                        
                        return $controller(...$args);
                    },
                    'permission_callback' => $route['permission_callback']
                ]
            );
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

        // Find matching route configuration
        $routePath = str_replace('/' . $this->namespace, '', $route);
        
        foreach ($this->routes as $routeConfig) {
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
