<?php

namespace Wappointment\Routes;

use Wappointment\Services\Settings;

abstract class AbstractRoutes
{
    protected $routes = [];
    protected $disabled_modern_api_verbs = true;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'restApiInit']);
    }

    public function getRoutes()
    {
        return $this->routes;
    }
    public function get()
    {
        return [[
            'ns' => '\\Wappointment\\Controllers\\',
            'routes' => $this->routes
        ]];
    }

    public function restApiInit()
    {
        foreach ($this->prepareRoutes() as $access => $actions) {
            foreach ($actions as $http_method => $route_controller_action) {
                foreach ($route_controller_action as $route => $controller_method_args) {
                    $controller_name = $controller_method_args['ns'] . $controller_method_args['controller'];

                    if (!class_exists($controller_name)) {
                        continue;
                    }
                    $apiVersion = empty($controller_method_args['version']) ? 'v1' : $controller_method_args['version'];
                    register_rest_route(
                        WAPPOINTMENT_SLUG . '/' . $apiVersion,
                        $route,
                        [
                            'methods' => $http_method,
                            'callback' => [new $controller_name(), 'tryExecute'],
                            'permission_callback' => [$this, 'canExecute' . ucfirst($access)],
                            'args' => (empty($controller_method_args['args'])) ?
                                [
                                    'method' => $controller_method_args['method'],
                                    'hint' => $controller_method_args['hint'] ?? false
                                ] : $controller_method_args['args'],
                        ]
                    );
                }
            }
        }
    }

    private function prepareRoutes()
    {
        $all_routes = [];

        $routes_loop = $this->get();

        foreach ($routes_loop as $routeObject) {
            foreach ($routeObject['routes'] as $access => $methodRoutes) {
                foreach ($methodRoutes as $method => $routes) {
                    if ($method == 'RESOURCE') {
                        foreach ($routes as $uri_portion => $resourceObject) {
                            if (empty($resourceObject['ns'])) {
                                $resourceObject['ns'] = $routeObject['ns'];
                            }
                            foreach ($resourceObject['methods'] as $resourceMethod) {
                                $all_routes[$access][$this->getHTTP($resourceMethod)][$this->getURIPortion($resourceMethod, $uri_portion)] = [
                                    'ns' => $resourceObject['ns'],
                                    'controller' => $resourceObject['controller'],
                                    'method' => strtoupper($resourceMethod) == 'POST' ? 'save' : $resourceMethod,
                                ];
                            }
                        }
                    } else {
                        foreach ($routes as $uri_portion => $resourceObject) {
                            if (empty($resourceObject['ns'])) {
                                $resourceObject['ns'] = $routeObject['ns'];
                            }
                            $all_routes[$access][$this->getHTTP($method)][$this->getURIPortion($method, $uri_portion)] = $resourceObject;
                        }
                    }
                }
            }
        }

        return $all_routes;
    }


    public function replaceModernVerb($method)
    {

        if ($this->disabled_modern_api_verbs) {
            $replace_by_post = ['DELETE', 'PUT', 'PATCH'];
            if (in_array(strtoupper($method), $replace_by_post)) {
                return true;
            }
        }
        return false;
    }
    public function getURIPortion($method, $uri)
    {
        if ($this->replaceModernVerb($method)) {
            return $uri . '/' . strtolower($method);
        }
        return $uri;
    }

    public function getHTTP($method)
    {
        if ($this->replaceModernVerb($method)) {
            return 'POST';
        }
        return strtoupper($method);
    }

    public function canExecutePublic($args)
    {
        return true;
    }

    public function canExecuteAuthor($args)
    {
        return current_user_can('author');
    }

    public function canExecuteEditor($args)
    {
        return current_user_can('editor');
    }

    public function canExecuteAdministrator($args)
    {
        return current_user_can('administrator');
    }
}
