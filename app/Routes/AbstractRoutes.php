<?php

namespace Wappointment\Routes;

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
                            'args' => $this->getArgs($controller_method_args),
                        ]
                    );
                }
            }
        }
    }

    public function getArgs($controller_method_args)
    {
        $args = (empty($controller_method_args['args'])) ?
            [
                'wparams' => [
                    'method' => $controller_method_args['method'],
                ]
            ] : $controller_method_args['args'];

        if (!empty($args['wparams'])) {
            foreach (['hint', 'cap', 'paginated'] as $param_key) {
                if (!empty($controller_method_args[$param_key])) {
                    $args['wparams'][$param_key] = $controller_method_args[$param_key];
                }
            }
        }

        return $args;
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
        return $this->disabled_modern_api_verbs && in_array(strtoupper($method), ['DELETE', 'PUT', 'PATCH']);
    }

    public function getURIPortion($method, $uri)
    {
        return $this->replaceModernVerb($method) ? $uri . '/' . strtolower($method) : $uri;
    }

    public function getHTTP($method)
    {
        return $this->replaceModernVerb($method) ? 'POST' : strtoupper($method);
    }

    public function canExecutePublic($args)
    {
        return true;
    }

    public function canExecuteAdministrator($args)
    {
        return current_user_can('administrator') || current_user_can('wappointment_manager');
    }

    public function canExecuteMixed($request)
    {
        $cap = $this->getRequestCap($request);
        return $this->canExecuteAdministrator(false) || current_user_can(empty($cap) ? 'administrator' : $cap);
    }
    protected function getRequestCap($request)
    {
        $args = $request->get_attributes()['args'];
        return $this->capIsValid($args) ? $args['wparams']['cap'] : 'administrator';
    }

    protected function capIsValid($args)
    {
        return !empty($args['wparams'])
            && !empty($args['wparams']['cap']
                && in_array($args['wparams']['cap'], $this->getAllowedCaps()));
    }

    protected function getAllowedCaps()
    {
        return (new \Wappointment\Services\Permissions)->getCaps(true);
    }
}
