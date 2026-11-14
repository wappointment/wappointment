<?php
declare(strict_types=1);

function architectureClasses(string $directory): array
{
    $root = realpath(__DIR__ . '/../../app');
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory));
    $classes = [];
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $relative = substr($file->getPathname(), strlen($root) + 1, -4);
            $classes[$relative] = ['Wappointment\\' . str_replace('/', '\\', $relative)];
        }
    }
    ksort($classes);
    return $classes;
}

test('each concrete controller is invokable and injects exactly one UseCase', function (string $class) {
    $reflection = new ReflectionClass($class);
    if ($reflection->isAbstract()) {
        expect($reflection->isInstantiable())->toBeFalse();
        return;
    }
    $constructor = $reflection->getConstructor();
    expect($constructor)->not->toBeNull();
    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(1);
    $type = $parameters[0]->getType();
    expect($type)->toBeInstanceOf(ReflectionNamedType::class);
    expect($type->getName())->toStartWith('Wappointment\\UseCases\\');
    $methods = array_map(static fn(ReflectionMethod $method): string => $method->getName(), $reflection->getMethods(ReflectionMethod::IS_PUBLIC));
    sort($methods);
    expect($methods)->toBe(['__construct', '__invoke']);
    $source = file_get_contents($reflection->getFileName());
    expect($source)->toContain('$this->useCase->execute(')
        ->not->toContain('Wappointment\\Repositories\\', 'Wappointment\\Actions\\', 'Wappointment\\Booking\\BookingService');
})->with(architectureClasses('Controllers'));

test('UseCases expose execute and Actions expose handle', function (string $class) {
    $reflection = new ReflectionClass($class);
    $entryPoint = str_starts_with($class, 'Wappointment\\UseCases\\') ? 'execute' : 'handle';
    $methods = array_values(array_filter(
        $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
        static fn(ReflectionMethod $method): bool => !$method->isConstructor()
    ));
    expect($methods)->toHaveCount(1)->and($methods[0]->getName())->toBe($entryPoint);
    $source = file_get_contents($reflection->getFileName());
    expect($source)->not->toContain('Wappointment\\Controllers\\');
    if ($entryPoint === 'execute') {
        expect($source)->not->toContain(
            'WP_REST_Request', 'WP_REST_Response', 'JsonResponse', '$_GET', '$_POST',
            'Wappointment\\Repositories\\'
        );
    }
})->with(array_merge(architectureClasses('UseCases'), architectureClasses('Actions')));

test('each API operation uses its own controller UseCase and matching request and response contracts', function () {
    $routes = require __DIR__ . '/../../app/Routes/api.php';
    $controllers = array_column($routes, 'controller');
    expect(array_unique($controllers))->toHaveCount(count($routes));
    $useCases = [];
    $requests = [];
    $responses = [];
    foreach ($controllers as $controller) {
        $reflection = new ReflectionClass($controller);
        $type = $reflection->getConstructor()->getParameters()[0]->getType();
        $useCases[] = $type->getName();
        $invoke = $reflection->getMethod('__invoke');
        expect($invoke->getParameters())->toHaveCount(1);
        $request = $invoke->getParameters()[0]->getType()->getName();
        $response = $invoke->getReturnType()->getName();
        expect(is_subclass_of($request, \Wappointment\Http\Requests\RequestData::class))->toBeTrue();
        expect(is_subclass_of($response, \Wappointment\Http\Responses\ResponseData::class))->toBeTrue();
        $execute = new ReflectionMethod($type->getName(), 'execute');
        expect($execute->getParameters())->toHaveCount(1)
            ->and($execute->getParameters()[0]->getType()->getName())->toBe($request)
            ->and($execute->getReturnType()->getName())->toBe($response);
        expect(file_get_contents($reflection->getFileName()))->not->toContain(
            'WP_REST', 'WP_Error', 'JsonResponse', 'JsonErrorResponse', 'catch (', 'wp_send_json'
        );
        $requests[] = $request;
        $responses[] = $response;
    }
    expect(array_unique($useCases))->toHaveCount(count($routes));
    expect(array_unique($requests))->toHaveCount(count($routes));
    expect(array_unique($responses))->toHaveCount(count($routes));
});

test('Data contracts declare readonly constructor properties rather than payloads or manual schemas', function (string $class) {
    $reflection = new ReflectionClass($class);
    if ($reflection->isAbstract()) {
        expect($reflection->isInstantiable())->toBeFalse();
        return;
    }
    expect($reflection->getMethod('schema')->getDeclaringClass()->getName())->toBe(\Wappointment\System\Data::class);
    $parameters = $reflection->getConstructor()?->getParameters() ?? [];
    foreach ($parameters as $parameter) {
        $property = $reflection->getProperty($parameter->getName());
        expect($property->isPublic())->toBeTrue()
            ->and($property->isReadOnly())->toBeTrue()
            ->and($property->hasType())->toBeTrue();
    }
    expect(file_get_contents($reflection->getFileName()))->not->toContain(
        'private readonly array $values', 'function text(', 'function integer(', 'ApiSchemas',
        'WP_REST', 'sanitize_text_field', 'current_time('
    );
    expect($class::schema()['additionalProperties'])->toBeFalse();
})->with(array_merge(
    architectureClasses('Http/Requests'),
    architectureClasses('Http/Responses'),
    architectureClasses('Http/Data')
));
