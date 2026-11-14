<?php
declare(strict_types=1);

namespace Wappointment\Actions\Routes;

use Wappointment\Booking\BookingException;
use Wappointment\Http\ContractValidator;
use Wappointment\Http\Requests\RequestData;
use Wappointment\Http\Responses\ErrorResponseData;
use Wappointment\Http\Responses\ResponseData;
use Wappointment\System\Container;

class RegisterRestRoutesAction
{
    public function __construct(private Container $container) {}

    public function handle(string $namespace, array $routes): void
    {
        foreach ($routes as $route) {
            $method = new \ReflectionMethod($route['controller'], '__invoke');
            $parameters = $method->getParameters();
            $requestType = count($parameters) === 1 ? $parameters[0]->getType() : null;
            $responseType = $method->getReturnType();
            if (!$requestType instanceof \ReflectionNamedType || !$responseType instanceof \ReflectionNamedType
                || !is_subclass_of($requestType->getName(), RequestData::class)
                || !is_subclass_of($responseType->getName(), ResponseData::class)) {
                throw new \LogicException('REST controllers must accept one RequestData and return one ResponseData.');
            }
            $requestClass = $requestType->getName();
            $responseClass = $responseType->getName();
            $status = $route['status'] ?? 200;
            if (!is_int($status) || $status < 200 || $status > 299 || $status === 204) {
                throw new \LogicException('Data responses require a successful HTTP status with a body.');
            }
            register_rest_route($namespace, $route['path'], [
                'methods' => $route['method'],
                'permission_callback' => $route['permission_callback'],
                'callback' => function (\WP_REST_Request $request) use ($route, $requestClass, $responseClass, $status): \WP_REST_Response {
                    try {
                        $input = ContractValidator::validate((object) $this->input($request), $requestClass::schema(), 'request', true, true);
                        $requestData = $requestClass::from($input);
                        $controller = $this->container->make($route['controller']);
                        $responseData = $controller($requestData);
                        if (!$responseData instanceof $responseClass) {
                            throw new \UnexpectedValueException('Controller returned the wrong response contract.');
                        }
                        return new \WP_REST_Response(['success' => true, 'data' => $responseData->jsonSerialize()], $status);
                    } catch (BookingException $error) {
                        if ($error->status >= 500) {
                            error_log('Wappointment API: ' . $error->getMessage());
                        }
                        return $this->error('wappointment_booking', $error->getMessage(), $error->status);
                    } catch (\InvalidArgumentException $error) {
                        return $this->error('wappointment_invalid_request', $error->getMessage(), 400);
                    } catch (\RuntimeException $error) {
                        $code = $error->getCode();
                        if (in_array($code, [403, 404, 409], true)) {
                            return $this->error('wappointment_request_failed', $error->getMessage(), $code);
                        }
                        error_log('Wappointment API: ' . $error);
                        return $this->error('wappointment_internal_error', 'The request could not be completed. Please contact the site administrator.', 500);
                    } catch (\Throwable $error) {
                        error_log('Wappointment API: ' . $error);
                        return $this->error('wappointment_internal_error', 'The request could not be completed. Please contact the site administrator.', 500);
                    }
                },
            ]);
        }
    }

    private function input(\WP_REST_Request $request): array
    {
        $query = $request->get_query_params();
        unset($query['rest_route'], $query['_locale']);
        $body = [];
        $rawBody = $request->get_body();
        if ($rawBody !== null && $rawBody !== '') {
            $contentType = $request->get_content_type();
            if (!$contentType || !wp_is_json_media_type($contentType['value'])) {
                throw new \InvalidArgumentException('Expected an application/json request body.');
            }
            try {
                $decoded = json_decode($rawBody, false, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $error) {
                throw new \InvalidArgumentException('Malformed JSON request body.', 0, $error);
            }
            if (!$decoded instanceof \stdClass) {
                throw new \InvalidArgumentException('Expected request parameters as a JSON object.');
            }
            $body = get_object_vars($decoded);
        } elseif ($request->get_body_params() !== []) {
            throw new \InvalidArgumentException('Expected an application/json request body.');
        }
        $input = [];
        foreach ([$query, $body, $request->get_url_params()] as $source) {
            foreach ($source as $key => $value) {
                if (array_key_exists($key, $input) && $input[$key] !== $value
                    && !(((is_int($input[$key]) && is_string($value)) || (is_string($input[$key]) && is_int($value)))
                        && (string) $input[$key] === (string) $value)) {
                    throw new \InvalidArgumentException("Conflicting request parameter: {$key}.");
                }
                $input[$key] = $value;
            }
        }
        return $input;
    }

    private function error(string $code, string $message, int $status): \WP_REST_Response
    {
        $data = new ErrorResponseData(['code' => $code, 'message' => $message, 'data' => ['status' => $status]]);
        return new \WP_REST_Response($data->toArray(), $status);
    }
}
