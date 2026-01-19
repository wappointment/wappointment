<?php
declare(strict_types=1);

namespace Wappointment\Routes;

use Wappointment\Controllers\ApiController;

/**
 * REST API routes configuration
 */
class RestApi
{
    private ApiController $controller;
    private string $namespace = 'wappointment/v1';

    public function __construct()
    {
        $this->controller = new ApiController();
    }

    /**
     * Register REST API routes
     */
    public function register(): void
    {
        register_rest_route($this->namespace, '/page1', [
            'methods' => 'GET',
            'callback' => [$this->controller, 'getPage1Data'],
            'permission_callback' => [$this, 'checkPermissions']
        ]);

        register_rest_route($this->namespace, '/page2', [
            'methods' => 'GET',
            'callback' => [$this->controller, 'getPage2Data'],
            'permission_callback' => [$this, 'checkPermissions']
        ]);

        register_rest_route($this->namespace, '/page3', [
            'methods' => 'GET',
            'callback' => [$this->controller, 'getPage3Data'],
            'permission_callback' => [$this, 'checkPermissions']
        ]);

        register_rest_route($this->namespace, '/jobs', [
            'methods' => 'GET',
            'callback' => [$this->controller, 'getJobsData'],
            'permission_callback' => [$this, 'checkPermissions']
        ]);

        register_rest_route($this->namespace, '/clients', [
            'methods' => 'GET',
            'callback' => [$this->controller, 'getClientsData'],
            'permission_callback' => [$this, 'checkPermissions']
        ]);
    }

    /**
     * Check if user has permission to access API
     */
    public function checkPermissions(): bool
    {
        return current_user_can('manage_options');
    }
}
