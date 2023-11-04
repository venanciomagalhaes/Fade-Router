<?php

namespace Venancio\Fade\Core;

final class  MapRoutes
{
    /**
     * @var array $routes An array to store routes organized by HTTP methods.
     */
    private array $routes = [];

    /**
     * @var array|null $groupMiddleware An array to store middleware for a group of routes, or null if not defined.
     */
    private ?array $groupMiddleware = [];

    /**
     * @var array|null $singleMiddleware An array to store middleware for a single route, or null if not defined.
     */
    private ?array $singleMiddleware = [];

    /**
     * @var NamedRoutes $namedRoutes An instance of the NamedRoutes class to manage named routes.
     */
    private NamedRoutes $namedRoutes;

    /**
     * @var string|null $groupName The name of the current group of routes, or null if not set.
     */
    private ?string $groupName = null;

    /**
     * @var string|null $groupPrefix The prefix for the current group of routes, or null if not set.
     */
    private ?string $groupPrefix = null;

    /**
     * @var string $lastRouteRegistered The name of the last route that was registered.
     */
    private string $lastRouteRegistered;


    /**
     * Constructor for the MapRoutes class.
     *
     * Initializes a new instance of the class and creates an instance of the NamedRoutes class
     * for managing named routes.
     */
    public function __construct()
    {
        $this->namedRoutes = new NamedRoutes();
    }


    /**
     * Get all registered routes organized by HTTP method.
     *
     * @return array An array of registered routes organized by their respective HTTP methods.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get registered routes for a specific HTTP method.
     *
     * @param string $method The HTTP method (e.g., "GET", "POST") for which to retrieve routes.
     *
     * @return array An array of registered routes for the specified HTTP method, or null if no routes are found.
     */
    public function getRoutesByMethod(string $method): ?array
    {
        $routes = $this->getRoutes();
        return $routes[$method];
    }

    /**
     * Get a route with its optional group prefix.
     *
     * @param string $route The route path without a prefix.
     *
     * @return string The route path with the group prefix, if defined; otherwise, the original route.
     */
    private function getRouteWithPrefix(string $route): string
    {
        return $this->groupPrefix ? "{$this->groupPrefix}{$route}" : $route;
    }

    /**
     * Define and assign middlewares to a specific route.
     *
     * This method checks if group or single middlewares are defined and assigns them to a specific route
     * by updating the `middlewares` property of that route in the registered routes.
     *
     * @param string $method The HTTP method for the route.
     * @param string $route The route path.
     */
    private function defineMiddlewares(string $method, string $route): void
    {
        if ($this->groupMiddleware) {
            $this->routes[$method][$this->getRouteWithPrefix($route)]['middlewares'] = $this->groupMiddleware;
        }
        if ($this->singleMiddleware) {
            $this->routes[$method][$this->getRouteWithPrefix($route)]['middlewares'] = $this->singleMiddleware;
        }
    }

    /**
     * Clear single route middleware.
     *
     * This method sets the single route middleware to null, effectively clearing any previously assigned middleware
     * for a single route, ensuring that subsequent routes do not inherit it.
     */
    private function clearSingleMiddleware(): void
    {
        $this->setSingleMiddleware(null);
    }

    /**
     * Set and register a route with associated action and middleware.
     *
     * This method registers a route with its associated action and middleware. It takes into account
     * any group or single middlewares that may have been defined. It also updates the last route
     * registered and clears any single route middleware.
     *
     * @param string $method The HTTP method for the route (e.g., "GET", "POST").
     * @param string $route The route path.
     * @param array $action An array representing the route's action.
     */
    public function setRoute(string $method, string $route, array $action): void
    {
        $this->routes[$method][$this->getRouteWithPrefix($route)] = $action;
        $this->defineMiddlewares($method, $route);
        $this->setLastRouteRegistered($route);
        $this->clearSingleMiddleware();
    }

    /**
     * Set the name of the last route that was registered.
     *
     * This method updates the name of the last route that was registered. It is typically used to keep track
     * of the most recently registered route.
     *
     * @param string $lastRouteRegistered The name of the last route that was registered.
     */
    public function setLastRouteRegistered(string $lastRouteRegistered): void
    {
        $this->lastRouteRegistered = $lastRouteRegistered;
    }

    /**
     * Get an array of all named routes.
     *
     * This method retrieves an array containing all the named routes managed by the NamedRoutes class.
     *
     * @return array An array of named routes.
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes->getNamedRoutes();
    }

    /**
     * Get the URL of a named route with optional parameters.
     *
     * This method retrieves the URL of a named route by calling the corresponding method in the NamedRoutes class,
     * allowing you to specify parameters to replace placeholders in the route.
     *
     * @param string $name The name of the named route.
     * @param array $params An array of parameters to replace placeholders in the route (optional).
     *
     * @return string The URL of the named route with optional parameters.
     */
    public function getNamedRoute(string $name, $params = []): string
    {
      return $this->namedRoutes->getNamedRoute($name, $params);
    }

    /**
     * Set a named route with optional group and prefix names.
     *
     * This method allows you to define a named route, optionally prepending it with the group and prefix names.
     * It utilizes the `NamedRoutes` class to set the named route with the appropriate name and route path.
     *
     * @param string $name The name of the route.
     */
    public function setNamedRoute(string $name): void
    {
        $name =  $this->groupName ? "{$this->groupName}{$name}" : $name;
        $lastRoute = $this->groupPrefix ? "{$this->groupPrefix}{$this->lastRouteRegistered}": $this->lastRouteRegistered;
        $this->namedRoutes->setRoute($name,$lastRoute);
    }

    /**
     * Set single route middleware or clear it.
     *
     * This method is used to set middleware for a single route, or optionally clear any previously assigned
     * single route middleware. If middleware is provided, it is added to the list of single route middlewares.
     * If no middleware is provided, the single route middleware is cleared.
     *
     * @param array|null $middlewares An array of middleware for a single route, or null to clear it.
     */
    public function setSingleMiddleware(?array $middlewares): void
    {
        if($middlewares){
            foreach ($middlewares as $middleware){
                $this->singleMiddleware[] = $middleware;
            }
            return;
        }
        $this->singleMiddleware = null;
    }

    /**
     * Set group route middleware or clear it.
     *
     * This method is used to set middleware for a group of routes, or optionally clear any previously assigned
     * group route middleware. If an array of middleware is provided, it is added to the list of group route middlewares.
     * If no middleware is provided, the group route middleware is cleared.
     *
     * @param array|null $middlewares An array of middleware for a group of routes, or null to clear it.
     */
    public function setGroupMiddleware(?array $middlewares): void
    {
        if(is_array($middlewares)){
            foreach ($middlewares as $middleware){
                $this->groupMiddleware[] = $middleware;
            }
            return;
        }
        $this->groupMiddleware = null;
    }

    /**
     * Set the name of the current group of routes.
     *
     * This method updates the name of the current group of routes. You can use it to define a group name,
     * which can be useful for organizing and identifying routes.
     *
     * @param string|null $groupName The name of the group, or null to clear it.
     */
    public function setGroupName(?string $groupName): void
    {
        $this->groupName = $groupName;
    }

    /**
     * Set the prefix for the current group of routes.
     *
     * This method updates the prefix for the current group of routes. You can use it to define a common prefix
     * for all routes within a group, which can help in route organization and routing patterns.
     *
     * @param string|null $groupPrefix The prefix for the group, or null to clear it.
     */
    public function setGroupPrefix(?string $groupPrefix): void
    {
        $this->groupPrefix = $groupPrefix;
    }

}