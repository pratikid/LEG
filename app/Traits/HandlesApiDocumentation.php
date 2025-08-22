<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * Trait for handling API documentation operations
 */
trait HandlesApiDocumentation
{
    /**
     * Get the API documentation for all routes.
     *
     * @param string|null $prefix Filter routes by prefix
     * @param bool $groupByController Group routes by controller
     * @return array The API documentation
     */
    protected function getApiDocumentation(?string $prefix = null, bool $groupByController = true): array
    {
        $routes = Route::getRoutes();
        $documentation = [];
        
        foreach ($routes as $route) {
            // Skip non-API routes if prefix is specified
            if ($prefix && !Str::startsWith($route->uri(), $prefix)) {
                continue;
            }
            
            // Skip routes without controller actions
            $action = $route->getAction()['uses'] ?? null;
            if (!$action || !is_string($action) || !Str::contains($action, '@')) {
                continue;
            }
            
            // Get route documentation
            $routeDoc = $this->getRouteDocumentation($route);
            
            if ($groupByController) {
                // Group by controller
                $controller = $routeDoc['controller'];
                if (!isset($documentation[$controller])) {
                    $documentation[$controller] = [
                        'name' => $this->getControllerDisplayName($controller),
                        'description' => $this->getControllerDescription($controller),
                        'endpoints' => [],
                    ];
                }
                
                $documentation[$controller]['endpoints'][] = $routeDoc;
            } else {
                // Flat list
                $documentation[] = $routeDoc;
            }
        }
        
        return $documentation;
    }
    
    /**
     * Get the documentation for a specific route.
     *
     * @param \Illuminate\Routing\Route $route The route
     * @return array The route documentation
     */
    protected function getRouteDocumentation($route): array
    {
        $action = $route->getAction()['uses'] ?? null;
        [$controller, $method] = explode('@', $action);
        
        $reflection = new ReflectionMethod($controller, $method);
        $docComment = $reflection->getDocComment();
        
        $doc = [
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'name' => $route->getName(),
            'controller' => $controller,
            'action' => $method,
            'middleware' => $route->middleware(),
            'parameters' => $this->getRouteParameters($route),
            'description' => $this->parseDocBlockDescription($docComment),
            'tags' => $this->parseDocBlockTags($docComment),
        ];
        
        return $doc;
    }
    
    /**
     * Get the parameters for a route.
     *
     * @param \Illuminate\Routing\Route $route The route
     * @return array The route parameters
     */
    protected function getRouteParameters($route): array
    {
        $parameters = [];
        
        // Get path parameters
        preg_match_all('/\{(\w+)\??\}/', $route->uri(), $matches);
        
        if (isset($matches[1])) {
            foreach ($matches[1] as $parameter) {
                $parameters[$parameter] = [
                    'type' => 'path',
                    'required' => !Str::contains($route->uri(), "{{$parameter}?}"),
                    'description' => $this->getParameterDescription($route, $parameter),
                ];
            }
        }
        
        // Get method parameters from controller action
        $action = $route->getAction()['uses'] ?? null;
        if ($action && is_string($action) && Str::contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            
            $reflection = new ReflectionMethod($controller, $method);
            $methodParameters = $reflection->getParameters();
            
            foreach ($methodParameters as $param) {
                $paramName = $param->getName();
                
                // Skip the Request parameter
                if ($param->getType() && is_a($param->getType()->getName(), Request::class, true)) {
                    continue;
                }
                
                // Skip parameters already defined as path parameters
                if (isset($parameters[$paramName])) {
                    continue;
                }
                
                $parameters[$paramName] = [
                    'type' => 'query',
                    'required' => !$param->isOptional(),
                    'description' => $this->getParameterDescription($route, $paramName),
                    'default' => $param->isOptional() ? $param->getDefaultValue() : null,
                ];
            }
        }
        
        return $parameters;
    }
    
    /**
     * Get the description for a parameter from the doc block.
     *
     * @param \Illuminate\Routing\Route $route The route
     * @param string $parameter The parameter name
     * @return string|null The parameter description
     */
    protected function getParameterDescription($route, string $parameter): ?string
    {
        $action = $route->getAction()['uses'] ?? null;
        if (!$action || !is_string($action) || !Str::contains($action, '@')) {
            return null;
        }
        
        [$controller, $method] = explode('@', $action);
        
        $reflection = new ReflectionMethod($controller, $method);
        $docComment = $reflection->getDocComment();
        
        if (!$docComment) {
            return null;
        }
        
        // Look for @param tag for this parameter
        preg_match('/@param\s+([^\s]+)\s+\$' . preg_quote($parameter) . '\s+(.+)/s', $docComment, $matches);
        
        return isset($matches[2]) ? trim($matches[2]) : null;
    }
    
    /**
     * Parse the description from a doc block.
     *
     * @param string|false $docComment The doc comment
     * @return string|null The description
     */
    protected function parseDocBlockDescription($docComment): ?string
    {
        if (!$docComment) {
            return null;
        }
        
        // Remove comment markers and leading asterisks
        $docComment = preg_replace('/^\s*\/\*\*|\*\/\s*$/s', '', $docComment);
        $docComment = preg_replace('/^\s*\*\s*/m', '', $docComment);
        
        // Extract description (everything before the first @tag)
        $parts = preg_split('/^@/m', $docComment, 2);
        $description = trim($parts[0]);
        
        return $description ?: null;
    }
    
    /**
     * Parse the tags from a doc block.
     *
     * @param string|false $docComment The doc comment
     * @return array The tags
     */
    protected function parseDocBlockTags($docComment): array
    {
        if (!$docComment) {
            return [];
        }
        
        $tags = [];
        
        // Match all @tags
        preg_match_all('/@(\w+)\s+(.+?)(?=\s*@|\s*$)/s', $docComment, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $tagName = $match[1];
            $tagContent = trim($match[2]);
            
            // Special handling for certain tags
            switch ($tagName) {
                case 'param':
                    // Parse @param tag (type $name description)
                    if (preg_match('/^([^\s]+)\s+\$([^\s]+)\s*(.*)$/s', $tagContent, $paramMatch)) {
                        $tags['params'][] = [
                            'type' => $paramMatch[1],
                            'name' => $paramMatch[2],
                            'description' => trim($paramMatch[3]),
                        ];
                    }
                    break;
                    
                case 'return':
                    // Parse @return tag (type description)
                    if (preg_match('/^([^\s]+)\s*(.*)$/s', $tagContent, $returnMatch)) {
                        $tags['return'] = [
                            'type' => $returnMatch[1],
                            'description' => trim($returnMatch[2]),
                        ];
                    }
                    break;
                    
                case 'throws':
                    // Parse @throws tag (type description)
                    if (preg_match('/^([^\s]+)\s*(.*)$/s', $tagContent, $throwsMatch)) {
                        $tags['throws'][] = [
                            'type' => $throwsMatch[1],
                            'description' => trim($throwsMatch[2]),
                        ];
                    }
                    break;
                    
                default:
                    // Generic tag handling
                    if (isset($tags[$tagName])) {
                        // If the tag already exists, convert it to an array
                        if (!is_array($tags[$tagName])) {
                            $tags[$tagName] = [$tags[$tagName]];
                        }
                        $tags[$tagName][] = $tagContent;
                    } else {
                        $tags[$tagName] = $tagContent;
                    }
                    break;
            }
        }
        
        return $tags;
    }
    
    /**
     * Get the display name for a controller.
     *
     * @param string $controller The controller class name
     * @return string The display name
     */
    protected function getControllerDisplayName(string $controller): string
    {
        $baseName = class_basename($controller);
        
        // Remove 'Controller' suffix
        $name = preg_replace('/Controller$/', '', $baseName);
        
        // Convert to title case with spaces
        return Str::title(Str::snake($name, ' '));
    }
    
    /**
     * Get the description for a controller from its class doc block.
     *
     * @param string $controller The controller class name
     * @return string|null The controller description
     */
    protected function getControllerDescription(string $controller): ?string
    {
        try {
            $reflection = new ReflectionClass($controller);
            $docComment = $reflection->getDocComment();
            
            return $this->parseDocBlockDescription($docComment);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Generate OpenAPI specification for the API.
     *
     * @param string $title The API title
     * @param string $version The API version
     * @param string|null $prefix Filter routes by prefix
     * @return array The OpenAPI specification
     */
    protected function generateOpenApiSpec(string $title, string $version, ?string $prefix = null): array
    {
        $routes = Route::getRoutes();
        $paths = [];
        $components = [
            'schemas' => [],
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
        ];
        
        foreach ($routes as $route) {
            // Skip non-API routes if prefix is specified
            if ($prefix && !Str::startsWith($route->uri(), $prefix)) {
                continue;
            }
            
            // Skip routes without controller actions
            $action = $route->getAction()['uses'] ?? null;
            if (!$action || !is_string($action) || !Str::contains($action, '@')) {
                continue;
            }
            
            // Generate path item
            $pathItem = $this->generateOpenApiPathItem($route);
            
            // Add path to paths object
            $uri = '/' . ltrim(preg_replace('/\{(\w+)\??\}/', '{$1}', $route->uri()), '/');
            
            if (isset($paths[$uri])) {
                // Merge with existing path item
                $paths[$uri] = array_merge($paths[$uri], $pathItem);
            } else {
                $paths[$uri] = $pathItem;
            }
        }
        
        // Build the OpenAPI spec
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $title,
                'version' => $version,
                'description' => 'API documentation generated automatically',
            ],
            'servers' => [
                ['url' => url('/')],
            ],
            'paths' => $paths,
            'components' => $components,
        ];
        
        return $spec;
    }
    
    /**
     * Generate an OpenAPI path item for a route.
     *
     * @param \Illuminate\Routing\Route $route The route
     * @return array The OpenAPI path item
     */
    protected function generateOpenApiPathItem($route): array
    {
        $pathItem = [];
        
        $action = $route->getAction()['uses'] ?? null;
        [$controller, $method] = explode('@', $action);
        
        $reflection = new ReflectionMethod($controller, $method);
        $docComment = $reflection->getDocComment();
        
        $description = $this->parseDocBlockDescription($docComment);
        $tags = $this->parseDocBlockTags($docComment);
        
        // Determine if route requires authentication
        $requiresAuth = in_array('auth:api', $route->middleware()) || 
                        in_array('auth:sanctum', $route->middleware());
        
        // Get parameters
        $parameters = $this->getOpenApiParameters($route);
        
        // Get request body
        $requestBody = $this->getOpenApiRequestBody($tags);
        
        // Get responses
        $responses = $this->getOpenApiResponses($tags);
        
        // Create operation object for each HTTP method
        foreach ($route->methods() as $httpMethod) {
            // Skip HEAD method as it's typically handled automatically
            if ($httpMethod === 'HEAD') {
                continue;
            }
            
            $httpMethod = strtolower($httpMethod);
            
            $operation = [
                'summary' => $tags['summary'] ?? $this->generateOperationSummary($route, $httpMethod),
                'description' => $description,
                'tags' => [$this->getControllerDisplayName($controller)],
                'parameters' => $parameters,
                'responses' => $responses,
            ];
            
            // Add request body for methods that can have one
            if (in_array($httpMethod, ['post', 'put', 'patch']) && $requestBody) {
                $operation['requestBody'] = $requestBody;
            }
            
            // Add security requirement if authentication is required
            if ($requiresAuth) {
                $operation['security'] = [
                    ['bearerAuth' => []],
                ];
            }
            
            $pathItem[$httpMethod] = $operation;
        }
        
        return $pathItem;
    }
    
    /**
     * Generate OpenAPI parameters for a route.
     *
     * @param \Illuminate\Routing\Route $route The route
     * @return array The OpenAPI parameters
     */
    protected function getOpenApiParameters($route): array
    {
        $parameters = [];
        
        // Get path parameters
        preg_match_all('/\{(\w+)\??\}/', $route->uri(), $matches);
        
        if (isset($matches[1])) {
            foreach ($matches[1] as $parameter) {
                $required = !Str::contains($route->uri(), "{{$parameter}?}");
                $description = $this->getParameterDescription($route, $parameter);
                
                $parameters[] = [
                    'name' => $parameter,
                    'in' => 'path',
                    'required' => $required,
                    'description' => $description,
                    'schema' => [
                        'type' => 'string',
                    ],
                ];
            }
        }
        
        // Add query parameters based on doc block
        $action = $route->getAction()['uses'] ?? null;
        if ($action && is_string($action) && Str::contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            
            $reflection = new ReflectionMethod($controller, $method);
            $docComment = $reflection->getDocComment();
            $tags = $this->parseDocBlockTags($docComment);
            
            if (isset($tags['params'])) {
                foreach ($tags['params'] as $param) {
                    // Skip parameters that are already in the path
                    if (in_array($param['name'], $matches[1] ?? [])) {
                        continue;
                    }
                    
                    // Skip the request parameter
                    if ($param['type'] === 'Request' || $param['type'] === '\Illuminate\Http\Request') {
                        continue;
                    }
                    
                    $parameters[] = [
                        'name' => $param['name'],
                        'in' => 'query',
                        'description' => $param['description'],
                        'schema' => $this->getOpenApiTypeFromDocType($param['type']),
                    ];
                }
            }
        }
        
        return $parameters;
    }
    
    /**
     * Generate an OpenAPI request body from doc block tags.
     *
     * @param array $tags The doc block tags
     * @return array|null The OpenAPI request body
     */
    protected function getOpenApiRequestBody(array $tags): ?array
    {
        if (!isset($tags['requestBody'])) {
            return null;
        }
        
        $content = [];
        
        // Default to JSON content type
        $content['application/json'] = [
            'schema' => [
                'type' => 'object',
                'properties' => [],
            ],
        ];
        
        // Add properties from @property tags
        if (isset($tags['property'])) {
            $properties = is_array($tags['property']) ? $tags['property'] : [$tags['property']];
            
            foreach ($properties as $property) {
                if (preg_match('/^([^\s]+)\s+\$([^\s]+)\s*(.*)$/s', $property, $matches)) {
                    $propertyType = $matches[1];
                    $propertyName = $matches[2];
                    $propertyDescription = trim($matches[3]);
                    
                    $content['application/json']['schema']['properties'][$propertyName] = array_merge(
                        $this->getOpenApiTypeFromDocType($propertyType),
                        ['description' => $propertyDescription]
                    );
                }
            }
        }
        
        return [
            'description' => $tags['requestBody'],
            'required' => true,
            'content' => $content,
        ];
    }
    
    /**
     * Generate OpenAPI responses from doc block tags.
     *
     * @param array $tags The doc block tags
     * @return array The OpenAPI responses
     */
    protected function getOpenApiResponses(array $tags): array
    {
        $responses = [];
        
        // Add responses from @response tags
        if (isset($tags['response'])) {
            $responseTags = is_array($tags['response']) ? $tags['response'] : [$tags['response']];
            
            foreach ($responseTags as $responseTag) {
                if (preg_match('/^(\d+)\s+(.*)$/s', $responseTag, $matches)) {
                    $statusCode = $matches[1];
                    $description = trim($matches[2]);
                    
                    $responses[$statusCode] = [
                        'description' => $description,
                    ];
                    
                    // Add content for success responses
                    if ($statusCode >= 200 && $statusCode < 300) {
                        $responses[$statusCode]['content'] = [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                ],
                            ],
                        ];
                    }
                }
            }
        }
        
        // Add default responses if none specified
        if (empty($responses)) {
            $responses = [
                '200' => [
                    'description' => 'Successful operation',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                            ],
                        ],
                    ],
                ],
                '401' => [
                    'description' => 'Unauthorized',
                ],
                '403' => [
                    'description' => 'Forbidden',
                ],
                '404' => [
                    'description' => 'Not found',
                ],
                '422' => [
                    'description' => 'Validation error',
                ],
                '500' => [
                    'description' => 'Server error',
                ],
            ];
        }
        
        return $responses;
    }
    
    /**
     * Convert a PHP/doc block type to an OpenAPI type.
     *
     * @param string $docType The PHP/doc block type
     * @return array The OpenAPI type definition
     */
    protected function getOpenApiTypeFromDocType(string $docType): array
    {
        $docType = strtolower($docType);
        
        // Handle arrays
        if (Str::endsWith($docType, '[]') || Str::startsWith($docType, 'array<')) {
            return [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                ],
            ];
        }
        
        // Map PHP types to OpenAPI types
        $typeMap = [
            'int' => ['type' => 'integer'],
            'integer' => ['type' => 'integer'],
            'float' => ['type' => 'number', 'format' => 'float'],
            'double' => ['type' => 'number', 'format' => 'double'],
            'string' => ['type' => 'string'],
            'bool' => ['type' => 'boolean'],
            'boolean' => ['type' => 'boolean'],
            'array' => ['type' => 'array', 'items' => ['type' => 'string']],
            'object' => ['type' => 'object'],
            'mixed' => ['type' => 'object'],
            'date' => ['type' => 'string', 'format' => 'date'],
            'datetime' => ['type' => 'string', 'format' => 'date-time'],
            'file' => ['type' => 'string', 'format' => 'binary'],
        ];
        
        return $typeMap[$docType] ?? ['type' => 'string'];
    }
    
    /**
     * Generate a summary for an operation based on the route and HTTP method.
     *
     * @param \Illuminate\Routing\Route $route The route
     * @param string $httpMethod The HTTP method
     * @return string The operation summary
     */
    protected function generateOperationSummary($route, string $httpMethod): string
    {
        $action = $route->getAction()['uses'] ?? null;
        [$controller, $method] = explode('@', $action);
        
        $controllerName = $this->getControllerDisplayName($controller);
        $resourceName = Str::singular($controllerName);
        
        // Generate summary based on HTTP method and controller method
        switch ($httpMethod) {
            case 'get':
                if ($method === 'index') {
                    return "Get all {$controllerName}";
                } elseif ($method === 'show') {
                    return "Get a specific {$resourceName}";
                }
                break;
                
            case 'post':
                if ($method === 'store') {
                    return "Create a new {$resourceName}";
                }
                break;
                
            case 'put':
            case 'patch':
                if ($method === 'update') {
                    return "Update a {$resourceName}";
                }
                break;
                
            case 'delete':
                if ($method === 'destroy') {
                    return "Delete a {$resourceName}";
                }
                break;
        }
        
        // Fallback to a generic summary
        return Str::title(str_replace('_', ' ', $method)) . ' ' . $resourceName;
    }
}