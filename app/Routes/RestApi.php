<?php
declare(strict_types=1);

namespace Wappointment\Routes;

use Wappointment\Actions\Routes\GetRoutesForJsAction;
use Wappointment\Actions\Routes\RegisterRestRoutesAction;
use Wappointment\Actions\Routes\SetCacheHeadersAction;
use Wappointment\Auth\AccessPolicy;

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
        private SetCacheHeadersAction $setCacheHeadersAction,
        private AccessPolicy $accessPolicy
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
        foreach (RouteTable::load() as $route) {
            $access = $route['access'];
            $route['permission_callback'] ??= fn(): bool => $this->accessPolicy->can($access);
            $this->routes[] = $route;
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
