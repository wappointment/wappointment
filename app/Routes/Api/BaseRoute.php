<?php
declare(strict_types=1);

namespace Wappointment\Routes\Api;

abstract class BaseRoute
{
    protected string $namespace = 'wappointment/v1';
    protected string $route;
    protected string $method = 'GET';
    protected bool $cacheable = false;

    abstract public function getRoute(): string;
    abstract public function getCallback(): callable;

    public function getConfig(): array
    {
        return [
            'methods' => $this->method,
            'callback' => $this->getCallback(),
            'permission_callback' => [$this, 'checkPermissions'],
            'args' => [
                'cacheable' => $this->cacheable
            ]
        ];
    }

    public function register(): void
    {
        register_rest_route($this->namespace, $this->getRoute(), $this->getConfig());
    }

    public function checkPermissions(): bool
    {
        return current_user_can('manage_options');
    }

    public function isCacheable(): bool
    {
        return $this->cacheable;
    }
}
