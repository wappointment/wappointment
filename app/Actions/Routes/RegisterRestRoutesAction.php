<?php
declare(strict_types=1);

namespace Wappointment\Actions\Routes;

use Wappointment\System\Container;

/**
 * Register REST API routes with WordPress
 */
class RegisterRestRoutesAction
{
    public function __construct(
        private Container $container
    ) {}

    public function handle(string $namespace, array $routes): void
    {
        foreach ($routes as $route) {
            register_rest_route(
                $namespace,
                $route['path'],
                [
                    'methods' => $route['method'],
                    'callback' => function(\WP_REST_Request $request) use ($route) {
                        $controller = $this->container->make($route['controller']);
                        
                        // Get method parameters
                        $reflection = new \ReflectionMethod($controller, '__invoke');
                        $params = $reflection->getParameters();
                        
                        $args = [];
                        foreach ($params as $param) {
                            $type = $param->getType();
                            if ($type && !$type->isBuiltin()) {
                                $className = $type->getName();
                                
                                // Special handling for WP_REST_Request
                                if ($className === 'WP_REST_Request') {
                                    $args[] = $request;
                                    continue;
                                }
                                
                                try {
                                    // Check if class has a static 'fromWpRequest' method
                                    if (method_exists($className, 'fromWpRequest')) {
                                        $args[] = $className::fromWpRequest($request);
                                    } elseif (method_exists($className, 'from')) {
                                        $args[] = $className::from($request);
                                    } else {
                                        // Resolve from container with request parameter
                                        $args[] = $this->container->make($className, ['request' => $request]);
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
}
