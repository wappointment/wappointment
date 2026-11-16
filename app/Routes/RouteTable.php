<?php
declare(strict_types=1);

namespace Wappointment\Routes;

/**
 * Pure normalization of the declarative route table in api.php.
 *
 * Shared by REST registration and the build-time JavaScript route generator so
 * both always agree on transport method and path. No WordPress calls here.
 */
final class RouteTable
{
    /**
     * Modern verbs are transported as POST with a verb suffix for hosting compatibility.
     */
    public const SUFFIXED_METHODS = ['DELETE', 'PUT', 'PATCH'];

    public static function load(): array
    {
        return self::normalize(require __DIR__ . '/api.php');
    }

    /**
     * @param list<array<string, mixed>> $config
     * @return list<array<string, mixed>>
     */
    public static function normalize(array $config): array
    {
        $routes = [];
        foreach ($config as $route) {
            $originalMethod = strtoupper($route['method'] ?? 'GET');
            $method = $originalMethod;
            $path = $route['path'];
            if (in_array($method, self::SUFFIXED_METHODS, true)) {
                $suffix = '/' . strtolower($method);
                if (!str_ends_with($path, $suffix)) {
                    $path .= $suffix;
                }
                $method = 'POST';
            }
            $routes[] = [
                'name' => $route['name'] ?? null,
                'method' => $method,
                'path' => $path,
                'controller' => $route['controller'],
                'access' => $route['access'] ?? 'manage',
                'status' => $route['status'] ?? 200,
                'cacheable' => $route['cacheable'] ?? true,
                'requires_schema' => $route['requires_schema'] ?? true,
                'permission_callback' => $route['permission_callback'] ?? null,
                'original_method' => $originalMethod,
                'original_path' => $route['path'],
            ];
        }
        return $routes;
    }
}
