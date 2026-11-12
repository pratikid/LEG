<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Trait for handling request operations
 */
trait HandlesRequestOperations
{
    /**
     * Get all input from the request.
     *
     * @param  Request  $request  The request
     * @return array The request input
     */
    protected function getAllInput(Request $request): array
    {
        return $request->all();
    }

    /**
     * Get a specific input value from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The input key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The input value
     */
    protected function getInput(Request $request, string $key, $default = null)
    {
        return $request->input($key, $default);
    }

    /**
     * Determine if the request has a specific input.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The input key
     * @return bool True if the input exists, false otherwise
     */
    protected function hasInput(Request $request, string $key): bool
    {
        return $request->has($key);
    }

    /**
     * Get a typed input value from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The input key
     * @param  string  $type  The expected type (string, int, bool, array)
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The input value cast to the specified type
     */
    protected function getTypedInput(Request $request, string $key, string $type, $default = null)
    {
        $value = $request->input($key, $default);

        switch ($type) {
            case 'string':
                return (string) $value;
            case 'int':
            case 'integer':
                return (int) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'array':
                return (array) $value;
            default:
                return $value;
        }
    }

    /**
     * Get a boolean input value from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The input key
     * @param  bool  $default  The default value if the key doesn't exist
     * @return bool The input value as a boolean
     */
    protected function getBoolInput(Request $request, string $key, bool $default = false): bool
    {
        return $this->getTypedInput($request, $key, 'bool', $default);
    }

    /**
     * Get an integer input value from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The input key
     * @param  int  $default  The default value if the key doesn't exist
     * @return int The input value as an integer
     */
    protected function getIntInput(Request $request, string $key, int $default = 0): int
    {
        return $this->getTypedInput($request, $key, 'int', $default);
    }

    /**
     * Get a string input value from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The input key
     * @param  string  $default  The default value if the key doesn't exist
     * @return string The input value as a string
     */
    protected function getStringInput(Request $request, string $key, string $default = ''): string
    {
        return $this->getTypedInput($request, $key, 'string', $default);
    }

    /**
     * Get an array input value from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The input key
     * @param  array  $default  The default value if the key doesn't exist
     * @return array The input value as an array
     */
    protected function getArrayInput(Request $request, string $key, array $default = []): array
    {
        return $this->getTypedInput($request, $key, 'array', $default);
    }

    /**
     * Get a float input value from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The input key
     * @param  float  $default  The default value if the key doesn't exist
     * @return float The input value as a float
     */
    protected function getFloatInput(Request $request, string $key, float $default = 0.0): float
    {
        return $this->getTypedInput($request, $key, 'float', $default);
    }

    /**
     * Get all query parameters from the request.
     *
     * @param  Request  $request  The request
     * @return array The query parameters
     */
    protected function getAllQueryParams(Request $request): array
    {
        return $request->query();
    }

    /**
     * Get a specific query parameter from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The query parameter key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The query parameter value
     */
    protected function getQueryParam(Request $request, string $key, $default = null)
    {
        return $request->query($key, $default);
    }

    /**
     * Get all route parameters from the request.
     *
     * @param  Request  $request  The request
     * @return array The route parameters
     */
    protected function getAllRouteParams(Request $request): array
    {
        return $request->route()->parameters();
    }

    /**
     * Get a specific route parameter from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The route parameter key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The route parameter value
     */
    protected function getRouteParam(Request $request, string $key, $default = null)
    {
        return $request->route($key) ?? $default;
    }

    /**
     * Get all headers from the request.
     *
     * @param  Request  $request  The request
     * @return array The headers
     */
    protected function getAllHeaders(Request $request): array
    {
        return $request->headers->all();
    }

    /**
     * Get a specific header from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The header key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The header value
     */
    protected function getHeader(Request $request, string $key, $default = null)
    {
        return $request->header($key, $default);
    }

    /**
     * Get the request method.
     *
     * @param  Request  $request  The request
     * @return string The request method
     */
    protected function getMethod(Request $request): string
    {
        return $request->method();
    }

    /**
     * Determine if the request method matches a specific method.
     *
     * @param  Request  $request  The request
     * @param  string  $method  The method to check
     * @return bool True if the request method matches, false otherwise
     */
    protected function isMethod(Request $request, string $method): bool
    {
        return $request->isMethod($method);
    }

    /**
     * Get the request URL.
     *
     * @param  Request  $request  The request
     * @return string The request URL
     */
    protected function getUrl(Request $request): string
    {
        return $request->url();
    }

    /**
     * Get the full request URL with query parameters.
     *
     * @param  Request  $request  The request
     * @return string The full request URL
     */
    protected function getFullUrl(Request $request): string
    {
        return $request->fullUrl();
    }

    /**
     * Get the request path.
     *
     * @param  Request  $request  The request
     * @return string The request path
     */
    protected function getPath(Request $request): string
    {
        return $request->path();
    }

    /**
     * Determine if the request path matches a specific pattern.
     *
     * @param  Request  $request  The request
     * @param  string  $pattern  The pattern to check
     * @return bool True if the request path matches, false otherwise
     */
    protected function isPath(Request $request, string $pattern): bool
    {
        return $request->is($pattern);
    }

    /**
     * Get the client IP address.
     *
     * @param  Request  $request  The request
     * @return string The client IP address
     */
    protected function getIp(Request $request): string
    {
        return $request->ip();
    }

    /**
     * Get the user agent.
     *
     * @param  Request  $request  The request
     * @return string The user agent
     */
    protected function getUserAgent(Request $request): string
    {
        return $request->userAgent() ?? '';
    }

    /**
     * Determine if the request is an AJAX request.
     *
     * @param  Request  $request  The request
     * @return bool True if the request is an AJAX request, false otherwise
     */
    protected function isAjax(Request $request): bool
    {
        return $request->ajax();
    }

    /**
     * Determine if the request is a JSON request.
     *
     * @param  Request  $request  The request
     * @return bool True if the request is a JSON request, false otherwise
     */
    protected function isJson(Request $request): bool
    {
        return $request->isJson();
    }

    /**
     * Determine if the request expects a JSON response.
     *
     * @param  Request  $request  The request
     * @return bool True if the request expects a JSON response, false otherwise
     */
    protected function expectsJson(Request $request): bool
    {
        return $request->expectsJson();
    }

    /**
     * Determine if the request is secure (HTTPS).
     *
     * @param  Request  $request  The request
     * @return bool True if the request is secure, false otherwise
     */
    protected function isSecure(Request $request): bool
    {
        return $request->secure();
    }

    /**
     * Get the request content type.
     *
     * @param  Request  $request  The request
     * @return string The request content type
     */
    protected function getContentType(Request $request): string
    {
        return $request->header('Content-Type', '');
    }

    /**
     * Get the request content.
     *
     * @param  Request  $request  The request
     * @return string The request content
     */
    protected function getContent(Request $request): string
    {
        return $request->getContent();
    }

    /**
     * Get the request JSON content.
     *
     * @param  Request  $request  The request
     * @param  string|null  $key  The JSON key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The JSON content
     */
    protected function getJson(Request $request, ?string $key = null, $default = null)
    {
        return $key ? $request->json($key, $default) : $request->json()->all();
    }

    /**
     * Get an uploaded file from the request.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The file key
     * @return \Illuminate\Http\UploadedFile|array|null The uploaded file
     */
    protected function getFile(Request $request, string $key)
    {
        return $request->file($key);
    }

    /**
     * Get all uploaded files from the request.
     *
     * @param  Request  $request  The request
     * @return array The uploaded files
     */
    protected function getAllFiles(Request $request): array
    {
        return $request->allFiles();
    }

    /**
     * Determine if the request has an uploaded file.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The file key
     * @return bool True if the request has an uploaded file, false otherwise
     */
    protected function hasFile(Request $request, string $key): bool
    {
        return $request->hasFile($key);
    }

    /**
     * Validate the request data.
     *
     * @param  Request  $request  The request
     * @param  array  $rules  The validation rules
     * @param  array  $messages  The validation messages
     * @param  array  $attributes  The validation attributes
     * @return array The validated data
     *
     * @throws ValidationException If validation fails
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [], array $attributes = []): array
    {
        return $request->validate($rules, $messages, $attributes);
    }

    /**
     * Validate the request data with a validator instance.
     *
     * @param  Request  $request  The request
     * @param  array  $rules  The validation rules
     * @param  array  $messages  The validation messages
     * @param  array  $attributes  The validation attributes
     * @return array The validated data
     *
     * @throws ValidationException If validation fails
     */
    protected function validateRequestWithValidator(Request $request, array $rules, array $messages = [], array $attributes = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get the validation errors from the request.
     *
     * @param  Request  $request  The request
     * @return array The validation errors
     */
    protected function getValidationErrors(Request $request): array
    {
        return $request->session()->get('errors') ? $request->session()->get('errors')->getBag('default')->getMessages() : [];
    }

    /**
     * Get the request locale.
     *
     * @param  Request  $request  The request
     * @return string The request locale
     */
    protected function getLocale(Request $request): string
    {
        return $request->getLocale();
    }

    /**
     * Set the request locale.
     *
     * @param  Request  $request  The request
     * @param  string  $locale  The locale
     */
    protected function setLocale(Request $request, string $locale): void
    {
        $request->setLocale($locale);
    }

    /**
     * Get the request format.
     *
     * @param  Request  $request  The request
     * @param  string  $default  The default format
     * @return string The request format
     */
    protected function getFormat(Request $request, string $default = 'html'): string
    {
        return $request->format($default);
    }

    /**
     * Get the bearer token from the request.
     *
     * @param  Request  $request  The request
     * @return string|null The bearer token
     */
    protected function getBearerToken(Request $request): ?string
    {
        return $request->bearerToken();
    }

    /**
     * Get the request fingerprint.
     *
     * @param  Request  $request  The request
     * @param  array  $extraData  Extra data to include in the fingerprint
     * @return string The request fingerprint
     */
    protected function getFingerprint(Request $request, array $extraData = []): string
    {
        $data = array_merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'path' => $request->path(),
        ], $extraData);

        return md5(json_encode($data));
    }

    /**
     * Get the request origin.
     *
     * @param  Request  $request  The request
     * @return string|null The request origin
     */
    protected function getOrigin(Request $request): ?string
    {
        return $request->header('Origin');
    }

    /**
     * Get the request referer.
     *
     * @param  Request  $request  The request
     * @return string|null The request referer
     */
    protected function getReferer(Request $request): ?string
    {
        return $request->header('Referer');
    }

    /**
     * Get the request host.
     *
     * @param  Request  $request  The request
     * @return string The request host
     */
    protected function getHost(Request $request): string
    {
        return $request->getHost();
    }

    /**
     * Get the request scheme.
     *
     * @param  Request  $request  The request
     * @return string The request scheme
     */
    protected function getScheme(Request $request): string
    {
        return $request->getScheme();
    }

    /**
n     * Get the request port.
     *
     * @param  Request  $request  The request
     * @return int The request port
     */
    protected function getPort(Request $request): int
    {
        return $request->getPort();
    }

    /**
     * Get the request URI.
     *
     * @param  Request  $request  The request
     * @return string The request URI
     */
    protected function getUri(Request $request): string
    {
        return $request->getRequestUri();
    }

    /**
     * Get the request query string.
     *
     * @param  Request  $request  The request
     * @return string The request query string
     */
    protected function getQueryString(Request $request): string
    {
        return $request->getQueryString() ?? '';
    }

    /**
     * Get the request base URL.
     *
     * @param  Request  $request  The request
     * @return string The request base URL
     */
    protected function getBaseUrl(Request $request): string
    {
        return $request->getBaseUrl();
    }

    /**
     * Get the request path info.
     *
     * @param  Request  $request  The request
     * @return string The request path info
     */
    protected function getPathInfo(Request $request): string
    {
        return $request->getPathInfo();
    }

    /**
     * Get the request route.
     *
     * @param  Request  $request  The request
     * @return \Illuminate\Routing\Route|null The request route
     */
    protected function getRoute(Request $request)
    {
        return $request->route();
    }

    /**
     * Get the request route name.
     *
     * @param  Request  $request  The request
     * @return string|null The request route name
     */
    protected function getRouteName(Request $request): ?string
    {
        return $request->route() ? $request->route()->getName() : null;
    }

    /**
     * Get the request route action.
     *
     * @param  Request  $request  The request
     * @return string|null The request route action
     */
    protected function getRouteAction(Request $request): ?string
    {
        return $request->route() ? $request->route()->getActionName() : null;
    }

    /**
     * Get the request session.
     *
     * @param  Request  $request  The request
     * @return \Illuminate\Session\Store The request session
     */
    protected function getSession(Request $request)
    {
        return $request->session();
    }

    /**
     * Get the request cookie.
     *
     * @param  Request  $request  The request
     * @param  string  $key  The cookie key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The cookie value
     */
    protected function getCookie(Request $request, string $key, $default = null)
    {
        return $request->cookie($key, $default);
    }

    /**
     * Get all cookies from the request.
     *
     * @param  Request  $request  The request
     * @return array The cookies
     */
    protected function getAllCookies(Request $request): array
    {
        return $request->cookies->all();
    }

    /**
     * Get the authenticated user from the request.
     *
     * @param  Request  $request  The request
     * @param  string|null  $guard  The authentication guard
     * @return \Illuminate\Contracts\Auth\Authenticatable|null The authenticated user
     */
    protected function getAuthenticatedUser(Request $request, ?string $guard = null)
    {
        return $request->user($guard);
    }

    /**
     * Determine if the request is authenticated.
     *
     * @param  Request  $request  The request
     * @param  string|null  $guard  The authentication guard
     * @return bool True if the request is authenticated, false otherwise
     */
    protected function isAuthenticated(Request $request, ?string $guard = null): bool
    {
        return $request->user($guard) !== null;
    }

    /**
     * Get the request input as a collection.
     *
     * @param  Request  $request  The request
     * @param  string|null  $key  The input key
     * @return \Illuminate\Support\Collection The input collection
     */
    protected function getInputAsCollection(Request $request, ?string $key = null)
    {
        return collect($key ? $request->input($key) : $request->all());
    }

    /**
     * Get the request input with only specific keys.
     *
     * @param  Request  $request  The request
     * @param  array  $keys  The keys to include
     * @return array The filtered input
     */
    protected function getOnlyInput(Request $request, array $keys): array
    {
        return $request->only($keys);
    }

    /**
     * Get the request input except specific keys.
     *
     * @param  Request  $request  The request
     * @param  array  $keys  The keys to exclude
     * @return array The filtered input
     */
    protected function getExceptInput(Request $request, array $keys): array
    {
        return $request->except($keys);
    }

    /**
     * Merge additional input into the request.
     *
     * @param  Request  $request  The request
     * @param  array  $input  The input to merge
     * @return Request The request with merged input
     */
    protected function mergeInput(Request $request, array $input): Request
    {
        return $request->merge($input);
    }

    /**
     * Replace the request input.
     *
     * @param  Request  $request  The request
     * @param  array  $input  The input to replace with
     * @return Request The request with replaced input
     */
    protected function replaceInput(Request $request, array $input): Request
    {
        return $request->replace($input);
    }

    /**
     * Get the request date.
     *
     * @param  Request  $request  The request
     * @return \Carbon\Carbon The request date
     */
    protected function getDate(Request $request)
    {
        return $request->date();
    }

    /**
     * Get the request instance.
     *
     * @return Request The request instance
     */
    protected function getRequest(): Request
    {
        return request();
    }
}
