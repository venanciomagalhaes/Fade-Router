<?php

namespace Venancio\Fade\Core;

use Venancio\Fade\Core\Exceptions\FallbackInternalServerErrorControllerUndefined;
use Venancio\Fade\Core\Exceptions\FallbackInternalServerErrorMethodUndefined;
use Venancio\Fade\Core\Exceptions\FallbackNotFoundControllerUndefined;
use Venancio\Fade\Core\Exceptions\FallbackNotFoundMethodUndefined;
use Venancio\Fade\Core\Exceptions\InvalidTypeMiddleware;
use Venancio\Fade\Core\Exceptions\NotFound;
use Venancio\Fade\Core\Interfaces\Middleware;
use Venancio\Fade\Core\Log\Logger;

final class Router
{

    /**
     * HTTP success response code.
     */
    const HTTP_SUCCESS_CODE = '200';

    /**
     * HTTP not found response code.
     */
    const HTTP_NOT_FOUND_CODE = '404';

    /**
     * HTTP internal server error response code.
     */
    const HTTP_INTERNAL_SERVER_ERROR_CODE = '500';

    /**
     * The HTTP request method (e.g., GET, POST, PUT, DELETE).
     * This property holds the request method used in the current HTTP request.
     */
    private string $requestMethod;

    /**
     * The request URI received in the HTTP request.
     * This property holds the request URI, which is the path and query parameters of the current request.
     */
    private string $requestUri;

    /**
     * An array to store parameters extracted from the URI during route matching.
     * This array is used to store parameters extracted from the URI when matching routes with placeholders.
     */
    private array $paramsURI = [];

    /**
     * The MapRoutes instance used for route mapping and management.
     * This is a static property shared among all instances of the Router class.
     */
    private static MapRoutes $mapRoutes;

    /**
     * The fallback controller and method for handling "not found" (404) errors.
     * These properties hold the names of the controller and method to be called when a route is not found.
     */
    private ?string $fallBackNotFoundController = null;
    private ?string $fallBackNotFoundMethod = null;

    /**
     * The fallback controller and method for handling internal server errors (500).
     * These properties hold the names of the controller and method to be called when an internal server error occurs.
     */
    private ?string $fallBackInternalServerErrorController = null;
    private ?string $fallBackInternalServerErrorMethod = null;

    /**
     * Constructor for the Router class.
     * Initializes the Router with request information and a new MapRoutes instance.
     */
    public function __construct()
    {
        $this->requestUri = $_GET['url'] ?? $_SERVER['REQUEST_URI'];
        $this->requestMethod = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        self::$mapRoutes = new MapRoutes();
    }

    /**
     * Define a GET route.
     *
     * @param string $route The route path.
     * @param array $action The action to be executed when the route is matched.
     * @return $this Returns the current Router instance for method chaining.
     */
    public function get(string $route, array $action): self
    {
        self::$mapRoutes->setRoute('GET', $route, $action);
        return $this;
    }

    /**
     * Define a POST route.
     *
     * @param string $route The route path.
     * @param array $action The action to be executed when the route is matched.
     * @return $this Returns the current Router instance for method chaining.
     */
    public function post(string $route, array $action):self
    {
        self::$mapRoutes->setRoute('POST', $route, $action);
        return $this;
    }

    /**
     * Define a PUT route.
     *
     * @param string $route The route path.
     * @param array $action The action to be executed when the route is matched.
     * @return $this Returns the current Router instance for method chaining.
     */
    public function put(string $route, array $action):self
    {
        self::$mapRoutes->setRoute('PUT', $route, $action);
        return $this;
    }

    /**
     * Define a DELETE route.
     *
     * @param string $route The route path.
     * @param array $action The action to be executed when the route is matched.
     * @return $this Returns the current Router instance for method chaining.
     */
    public function delete(string $route, array $action):self
    {
        self::$mapRoutes->setRoute('DELETE', $route, $action);
        return $this;
    }

    /**
     * Set the group name for routes within the current group.
     *
     * @param array $options The group options, including the 'name' setting.
     */
    private function setGroupName(array $options): void
    {
        if(isset($options['name'])){
            self::$mapRoutes->setGroupName($options['name']);
        }
    }

    /**
     * Set the prefix for routes within the current group.
     *
     * @param array $options The group options, including the 'prefix' setting.
     */
    private function setGroupPrefix(array $options): void
    {
        if(isset($options['prefix'])){
            self::$mapRoutes->setGroupPrefix($options['prefix']);
        }
    }

    /**
     * Clear the group settings, including group middleware, group prefix, and group name.
     */
    private function clearGroup(): void
    {
        self::$mapRoutes->setGroupMiddleware(null);
        self::$mapRoutes->setGroupPrefix(null);
        self::$mapRoutes->setGroupName(null);
    }

    /**
     * Verify the validity of each middleware in the provided array.
     *
     * @param array $middleware An array of middleware class names.
     * @throws InvalidTypeMiddleware
     */
    private function verifyMiddleware($middleware): void
    {
        foreach ($middleware as $middleware) {
            $this->isValidMiddleware($middleware);
        }
    }

    /**
     * Set the group middleware based on the provided options.
     *
     * @param array $options An array of options that may include 'middleware'.
     * @throws InvalidTypeMiddleware
     */
    private function setGroupMiddleware(array $options): void
    {
        if(isset($options['middleware'])){
            $this->verifyMiddleware($options['middleware']);
            self::$mapRoutes->setGroupMiddleware($options['middleware']);
        }
    }

    /**
     * Define a group of routes with common attributes.
     *
     * Groups allow you to define a set of routes with common middleware, a shared prefix, or a group name. This makes it easier to organize and apply settings to a set of related routes.

     * @param array $options An array of options for the group, including 'middleware', 'prefix', and 'name'.
     * @param \Closure $callback A closure that defines the routes within the group.
     */
    public function group(array $options, \Closure $callback): void
    {
        $this->setGroupMiddleware($options);
        $this->setGroupPrefix($options);
        $this->setGroupName($options);
        $callback();
        $this->clearGroup();
    }

    /**
     * Assign a name to the current route for later reference.
     *
     * This method assigns a name to the current route, making it easier to reference the route when generating URLs using named routes. Named routes provide a convenient way to generate URLs with route parameters without having to hardcode them in your application. You can use the `getNamedRoute` method to generate URLs based on named routes.
     *
     * @param string $name The name to assign to the current route.
     */
    public function name(string $name): void
    {
        self::$mapRoutes->setNamedRoute($name);
    }

    /**
     * Get the URL for a named route with optional parameters.
     *
     * This method retrieves the URL for a named route, allowing you to specify optional parameters. Named routes are useful for generating URLs dynamically based on route names and parameters.
     *
     * @param array $middlewares
     * @return Router The URL for the named route with optional parameters.
     * @throws InvalidTypeMiddleware
     */
    public function middleware(array $middlewares):self
    {
        $this->verifyMiddleware($middlewares);
        self::$mapRoutes->setSingleMiddleware($middlewares);
        return $this;
    }

    /**
     * Get the URL for a named route with optional parameters.
     *
     * This method retrieves the URL for a named route, allowing you to specify optional parameters. Named routes are useful for generating URLs dynamically based on route names and parameters.
     *
     * @param string $name The name of the named route.
     * @param array $params (Optional) An associative array of parameters to be used in the route. These parameters will replace placeholders in the route pattern.
     *
     * @return string The URL for the named route with optional parameters.
     */
    public static function getNamedRoute(string $name, array $params = []): string
    {
       return self::$mapRoutes->getNamedRoute($name, $params);
    }

    /**
     * Generates an HTML hidden input element for the HTTP PUT method.
     *
     * This method generates an HTML hidden input element that can be included in forms to simulate an HTTP PUT request. It's commonly used when you want to send a PUT request via a form submission.
     *
     * @return string The HTML representation of the hidden input element for the PUT method.
     */
    public static function methodPUT(): string
    {
        return "<input type='hidden' name='_method' value='PUT'>";
    }

    /**
     * Generates an HTML hidden input element for the HTTP DELETE method.
     *
     * This method generates an HTML hidden input element that can be included in forms to simulate an HTTP DELETE request. It's commonly used when you want to send a DELETE request via a form submission.
     *
     * @return string The HTML representation of the hidden input element for the DELETE method.
     */
    public static function methodDELETE(): string
    {
        return "<input type='hidden' name='_method' value='DELETE'>";
    }

    /**
     * Set the controller and method for the fallback not found handler.
     *
     * This method allows you to specify the controller and method that will be used as the fallback handler in case of a not found error (HTTP 404).
     *
     * @param string $controller The name of the controller class to handle not found errors.
     * @param string $method The name of the method within the controller to handle not found errors.
     */
    public function fallbackNotFound(string $controller, string $method): void
    {
        $this->fallBackNotFoundController = $controller;
        $this->fallBackNotFoundMethod = $method;
    }

    /**
     * Set the controller and method for the fallback internal server error handler.
     *
     * This method allows you to specify the controller and method that will be used as the fallback handler in case of an internal server error (HTTP 500).
     *
     * @param string $controller The name of the controller class to handle internal server errors.
     * @param string $method The name of the method within the controller to handle internal server errors.
     */
    public function fallbackInternalServerError(string $controller, string $method): void
    {
        $this->fallBackInternalServerErrorController = $controller;
        $this->fallBackInternalServerErrorMethod = $method;
    }

    /**
     * Decompose the current request URI into an array of its parts.
     *
     * This method takes the current request URI, trims it to remove leading and trailing slashes, and splits it into an array of its individual parts.
     *
     * @return array An array containing the parts of the current request URI.
     */
    private function decomposeCurrentRequestUri(): array
    {
        return explode('/', trim( $this->requestUri, '/'));
    }

    /**
     * Decompose a pattern URI into an array of its parts.
     *
     * This method takes a pattern URI and splits it into an array of its individual parts. It removes leading and trailing slashes before splitting.
     *
     * @param string $patternUri The pattern URI to be decomposed.
     * @return array An array containing the parts of the pattern URI.
     */
    private function decomposePatternUri(string $patternUri): array
    {
        return explode('/', trim($patternUri, '/'));
    }

    /**
     * Check if the number of parts in the current request URI and the pattern URI differs.
     *
     * This method checks if the number of parts in the current request URI differs from the number of parts in the pattern URI of a registered route. If the number of parts does not match, it returns true, indicating that the URIs are not compatible. Otherwise, it returns false, meaning that the URIs have a matching number of parts.
     *
     * @param array $uriParts The parts from the current request URI.
     * @param array $patternParts The parts from the pattern URI of a registered route.
     * @return bool True if the number of parts differs, false if they match.
     */
    private function notIsCompatibleUriAndPattern(array $uriParts, array $patternParts): bool
    {
        return count($uriParts) !== count($patternParts);
    }

    /**
     * Check if a part of the current request URI is equal to the corresponding part of the pattern URI.
     *
     * This method compares a specific part of the current request URI with the corresponding part of the pattern URI from a registered route. It checks if the two parts are equal. If they are equal, the method returns true, indicating that the specific parts match. Otherwise, it returns false.
     *
     * @param mixed $patternParts The part from the pattern URI of a registered route.
     * @param mixed $uriParts The part from the current request URI.
     * @return bool True if the parts are equal, false otherwise.
     */
    private function isUriPartEqualsUriPatternsPart($patternParts, $uriParts): bool
    {
        return $patternParts === $uriParts;
    }

    /**
     * Check if the URI parts match the pattern parts of a route.
     *
     * This method compares the parts of the current request URI with the parts of a pattern URI from a registered route. It checks if the parts match and captures any parameters enclosed in curly braces. If the parts match and the parameters are extracted successfully, the method returns true, indicating that the route is compatible with the current request URI. Otherwise, it returns false.
     *
     * @param array $uriParts Array of parts from the current request URI.
     * @param array $patternParts Array of parts from the pattern URI of a registered route.
     * @return bool True if the URI parts match the pattern parts, false otherwise.
     */
    private function isCompatibleParams(array $uriParts, array $patternParts): bool
    {
        $match = true;
        for ($i = 0; $i < count($uriParts); $i++) {
            if ($this->isUriPartEqualsUriPatternsPart($patternParts[$i], $uriParts[$i])) continue;
            if (preg_match('/\{(\w+)\}/', $patternParts[$i], $matches)) {
                $this->paramsURI[] = $uriParts[$i];
                continue;
            }
            $match = false;
            break;
        }
        return $match;
    }

    /**
     * Check if the current request URI corresponds to a valid route.
     *
     * This method is responsible for validating whether the current request URI matches a defined route for the current
     * request method (GET, POST, PUT, DELETE). It goes through the registered routes for the request method and compares
     * the URI parts to the pattern parts of each route. If a matching route is found, the request URI is updated accordingly,
     * and the method returns true, indicating that the route is valid. Otherwise, it returns false.
     *
     * @return bool True if a valid route is found, false otherwise.
     */
    private function isValidRoute(): bool
    {
        $routesByMethod = self::$mapRoutes->getRoutesByMethod($this->requestMethod);
        if($routesByMethod){
            foreach ($routesByMethod as $patternUri => $action) {
                $uriParts = $this->decomposeCurrentRequestUri();
                $patternParts = $this->decomposePatternUri($patternUri);
                if ($this->notIsCompatibleUriAndPattern($uriParts, $patternParts))  continue;
                if ($this->isCompatibleParams($uriParts, $patternParts)) {
                    $this->requestUri = $patternUri;
                    break;
                }
            }
            return isset (self::$mapRoutes->getRoutesByMethod($this->requestMethod)[$this->requestUri]);
        }
        return false;
    }


    /**
     * Verify the presence of fallback definitions for both not found and internal server error scenarios.
     *
     * This method checks if the fallback controllers and methods for not found and internal server error scenarios are defined.
     * If any of these properties are empty or null, it raises the corresponding exceptions to handle these situations.
     * The exceptions are registered in the Logger instance for error tracking.
     */
    private function verifyFallBackDefined(): void
    {
        $this->verifyProperty(
            $this->fallBackNotFoundController,
            FallbackNotFoundControllerUndefined::class
        );
        $this->verifyProperty(
            $this->fallBackNotFoundMethod,
            FallbackNotFoundMethodUndefined::class
        );
        $this->verifyProperty(
            $this->fallBackInternalServerErrorController,
            FallbackInternalServerErrorControllerUndefined::class
        );
        $this->verifyProperty(
            $this->fallBackInternalServerErrorMethod,
            FallbackInternalServerErrorMethodUndefined::class
        );
    }

    /**
     * Verify the presence of a property and raise an exception if it's empty or null.
     *
     * This method is responsible for checking if a specific property is empty or null and, if so, it raises the corresponding exception specified by the exception class. It registers the exception in the Logger instance for error tracking.
     *
     * @param mixed $property The property to be checked.
     * @param string $exceptionClass The class name of the exception to be thrown.
     *
     * @throws \Exception The specified exception when the property is empty or null.
     */
    private function verifyProperty($property,string $exceptionClass): void
    {
        if (empty($property)) {
            $exception = new $exceptionClass();
            Logger::getInstance()->register($exception);
            throw $exception ;
        }
    }

    /**
     * Retrieves the parameters to be passed to controllers from the URI.
     *
     * This method retrieves the parameters extracted from the current request's URI and returns them as an array.
     * These parameters are typically used as arguments when invoking controller methods.
     *
     * @param \Throwable $throwable
     * @return void
     */
    private function execFallBackInternalServerError(\Throwable $throwable): void
    {
        call_user_func_array([new $this->fallBackInternalServerErrorController,  $this->fallBackInternalServerErrorMethod], [$throwable]);
    }

    /**
     * Retrieves the parameters to be passed to controllers from the URI.
     *
     * This method retrieves the parameters extracted from the current request's URI and returns them as an array.
     * These parameters are typically used as arguments when invoking controller methods.
     *
     * @return array An array containing parameters extracted from the URI.
     */
    private function getParamsToControllers(): array
    {
        return [...$this->paramsURI];
    }

    /**
     * Checks if the given action has middleware defined.
     *
     * This method checks if the given action, which is typically a route's associated action, has middleware defined. It examines
     * the action array for the presence of a 'middlewares' key. If the 'middlewares' key is present, it indicates that middleware
     * is defined for the action, and the method returns true. Otherwise, it returns false.
     *
     * @param array $action The action array associated with a route.
     * @return bool True if middleware is defined in the action, false otherwise.
     */
    private function hasMiddleware(array $action): bool
    {
        return array_key_exists('middlewares', $action);
    }

    /**
     * Validates and checks if a given middleware is of the correct type.
     *
     * This method is responsible for validating and checking if a given middleware class is of the correct type. It creates an
     * instance of the middleware class and ensures it implements the `Middleware` interface. If the middleware is not of the
     * expected type, it throws an `InvalidTypeMiddleware` exception.
     *
     * @param string $middleware The class name of the middleware to be validated.
     * @throws InvalidTypeMiddleware If the middleware is not of the correct type (does not implement the `Middleware` interface).
     */
    private function isValidMiddleware(string $middleware): void
    {
        $middleware = new $middleware;
        if( $middleware instanceof Middleware){
            return;
        }
        $exception = new InvalidTypeMiddleware();
        Logger::getInstance()->register($exception);
        throw $exception;
    }

    /**
     * Dispatch and execute a list of middlewares in order.
     *
     * This method is responsible for dispatching and executing a list of middlewares in the specified order. It instantiates
     * each middleware class, sets parameters, and calls the `handle` method on each middleware.
     *
     * @param array $arrayMiddlewares An array of middleware class names.
     * @return void
     */
    private function dispatchMiddleware(array $arrayMiddlewares): void
    {
        foreach ($arrayMiddlewares as $middleware) {
            /**
             * @var Middleware $middleware
             */
            $middleware = new $middleware();
            $middleware->setParams($this->getParamsToControllers());
            $middleware->handle();
        }
    }

    /**
     * Execute the middlewares defined for a matched route.
     *
     * This method is responsible for executing the middlewares defined for a matched route, invoking each middleware
     * in the specified order.
     *
     * @param array $action An array containing the route action with middleware information.
     * @return void
     */
    private function execMiddlewares(array $action): void
    {
        if ($this->hasMiddleware($action)) {
            $this->dispatchMiddleware($action['middlewares']);
        }
    }

    /**
     * Execute the action defined for a matched route.
     *
     * This method is responsible for executing the action defined for a matched route, invoking the specified controller
     * method with the necessary parameters.
     *
     * @param array $action An array containing the controller and method to be called.
     * @return void
     */
    private function execAction(array $action): void
    {
        $controller = $action[0];
        $method = $action[1];
        call_user_func_array([new $controller, $method], $this->getParamsToControllers());
    }

    /**
     * Check if the current request URI matches the provided route.
     *
     * This method compares the current request URI with a provided route to determine if they are an exact match.
     *
     * @param string $route The route to compare with the current request URI.
     * @return bool True if the current request URI is an exact match to the provided route, false otherwise.
     */
    private function isCurrentURI(string $route): bool
    {
        return $this->requestUri == $route;
    }

    /**
     * Execute the request by processing the matching route and associated actions.
     *
     * This method processes the current HTTP request by matching it with the appropriate route and executing any associated middleware and action. If a route is found and processed successfully, it returns the HTTP response code '200' for success. If a 404 (Not Found) error occurs, it falls back to handling the error. If a different exception occurs, it falls back to handling a 500 (Internal Server Error) and logs the exception.
     *
     * @return string The HTTP response code ('200' for success, '404' for not found, '500' for internal server error).
     */
    private function exec(): string
    {
       try{
           foreach (self::$mapRoutes->getRoutesByMethod($this->requestMethod) as $route => $action) {
               if ($this->isCurrentURI($route)) {
                   $this->execMiddlewares($action);
                   $this->execAction($action);
                   return self::HTTP_SUCCESS_CODE;
               }
           }
       } catch (NotFound $exception) {
           Logger::getInstance()->register($exception);
           $this->execFallBackNotFound();
           return self::HTTP_NOT_FOUND_CODE;
       } catch (\Throwable $throwable){
           Logger::getInstance()->register($throwable);
           $this->execFallBackInternalServerError($throwable);
           return self::HTTP_INTERNAL_SERVER_ERROR_CODE;
       }
    }

    /**
     * Execute the fallback action for handling a 404 (Not Found) error.
     *
     * This method calls the specified controller and method to handle a 404 error. It returns the HTTP response code '404' after executing the fallback action.
     *
     * @return string The HTTP response code '404' for not found.
     */
    private function execFallBackNotFound(): string
    {
        call_user_func_array([new $this->fallBackNotFoundController,  $this->fallBackNotFoundMethod], []);
        return self::HTTP_NOT_FOUND_CODE;
    }

    /**
     * Determine the final response for the current HTTP request.
     *
     * This method checks if there is a valid route matching the request, and if so, it executes the associated middleware and action.
     * If no valid route is found, it falls back to handling a 404 error using the specified controller and method.
     *
     * @return string The HTTP response code ('200' for success, '404' for not found, '500' for internal server error).
     */
    private function finally(): string
    {
        return $this->isValidRoute() ? $this->exec() : $this->execFallBackNotFound();
    }

    /**
     * Dispatch the HTTP request to the appropriate controller and action.
     *
     * @return string|null The HTTP response code, or null if no route matches the request.
     */
    public function dispatch(): ?string
    {
        $this->verifyFallBackDefined();
        return $this->finally();
    }

    /**
     * Get all registered routes.
     *
     * @return array An array of registered routes.
     */
    public function getRoutes(): array
    {
        return self::$mapRoutes->getRoutes();
    }
}