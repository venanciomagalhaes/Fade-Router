<?php

namespace Venancio\Fade\Core;

use Venancio\Fade\Core\Exceptions\DuplicateNamedRoute;
use Venancio\Fade\Core\Exceptions\InsufficientArgumentsForTheRoute;
use Venancio\Fade\Core\Exceptions\UndefinedNamedRoute;
use Venancio\Fade\Core\Log\Logger;

final class NamedRoutes
{
    /**
     * An array to store registered named routes and their associated URLs.
     *
     * This property is used to store and manage named routes along with their associated URLs. Named routes are
     * registered using the `setRoute` method, and this array serves as a collection of registered routes where
     * the route name is the key and the associated URL is the value.
     *
     * @var array
     */
    private array $routes = [];

    /**
     * Retrieve all registered named routes and their associated URLs.
     *
     * This method returns an array containing all the registered named routes as keys and their associated
     * URLs as values.
     *
     * @return array An array of named routes and their associated URLs.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Retrieve the URL associated with a named route.
     *
     * This method allows you to retrieve the URL associated with a named route by providing the name of the route.
     * If the named route exists, it returns the associated URL; otherwise, it returns an empty string.
     *
     * @param string $name The name of the named route to retrieve the URL for.
     *
     * @return string The URL associated with the named route or an empty string if the route does not exist.
     */
    public function getRoute(string $name): string
    {
        return $this->routes[$name] ?? '';
    }

    /**
     * Check and throw an exception for a duplicate named route.
     *
     * This method checks if a route with the provided name already exists and throws a `DuplicateNamedRoute` exception
     * if a duplicate is found. It also registers the exception with the logger for tracking.
     *
     * @param string $name The name of the named route to check for duplicates.
     *
     * @throws DuplicateNamedRoute If a route with the same name already exists.
     */
    private function throwDuplicateNamedRoute(string $name): void
    {
        if (key_exists($name, $this->routes)) {
            $exception = new DuplicateNamedRoute();
            Logger::getInstance()->register($exception);
            throw $exception;
        }
    }

    /**
     * Set a named route with its associated URL.
     *
     * This method allows you to register a named route by associating it with its URL. Before setting the route,
     * it checks for duplicate routes with the same name to avoid naming conflicts.
     *
     * @param string $name The name of the route.
     * @param string $route The URL associated with the route.
     *
     * @throws DuplicateNamedRoute If a route with the same name already exists.
     */
    public function setRoute(string $name, string $route): void
    {
        $this->throwDuplicateNamedRoute($name);
        $this->routes[$name] = $route;
    }

    /**
     * Check if a named route does not exist.
     *
     * This method checks if a named route with the provided name does not exist in the registered routes.
     * It returns `true` if the named route is not found, and `false` if it exists.
     *
     * @param string $nameRoute The name of the named route to check.
     *
     * @return bool `true` if the named route does not exist, `false` if it exists.
     */
    private function namedRouteNotExists(string $nameRoute): bool
    {
        return !key_exists($nameRoute, $this->getRoutes());
    }

    /**
     * Check and throw an exception for an undefined named route.
     *
     * This method checks if a named route with the provided name does not exist and throws an `UndefinedNamedRoute` exception
     * if it is undefined.
     *
     * @param string $nameRoute The name of the named route to check.
     *
     * @throws UndefinedNamedRoute If the named route does not exist.
     */
    private function throwUndefinedNamedRoute($nameRoute): void
    {
        if($this->namedRouteNotExists($nameRoute))
            throw new UndefinedNamedRoute($nameRoute);
    }

    /**
     * Split a named route into its path segments.
     *
     * This method takes a named route and splits it into its individual path segments. It retrieves the
     * route URL associated with the given named route and then splits it into an array of segments based on '/'
     * as the separator.
     *
     * @param string $nameRoute The name of the named route for which to obtain path segments.
     *
     * @return array An array of path segments extracted from the named route.
     */
    private function getPartsOfRoute(string $nameRoute): array
    {
        return explode('/',  $this->getRoute($nameRoute));
    }

    /**
     * Extract and return route parameters from the route path segments.
     *
     * This method extracts and returns the route parameters (placeholders) from the provided route path segments.
     * It uses a regular expression to identify segments that match the pattern of route parameters and filters
     * them into an array of route parameters.
     *
     * @param array $partsOfRoute An array of route path segments.
     *
     * @return array An array of route parameters (placeholders) extracted from the route path.
     */
    private function getRouteParams(array $partsOfRoute): array
    {
        return array_filter($partsOfRoute, function ($part) {
            return preg_match('/\{(\w+)\}/', $part, $matches);
        });
    }

    /**
     * Count the number of parameters necessary for the route.
     *
     * This method calculates and returns the number of parameters necessary for the route based on the provided
     * `$routeParams` array. It simply counts the elements in the array to determine the required parameters.
     *
     * @param array $params An array of parameters necessary for the route.
     *
     * @return int The number of parameters required for the route.
     */
    private function countParamsInArgument(array $params): int
    {
        return count($params);
    }

    /**
     * Count the number of parameters necessary for the route.
     *
     * This method calculates and returns the number of parameters necessary for the route based on the provided
     * `$routeParams` array. It simply counts the elements in the array to determine the required parameters.
     *
     * @param array $routeParams An array of parameters necessary for the route.
     *
     * @return int The number of parameters required for the route.
     */
    private function countParametersNecessaryForTheRoute(array $routeParams): int
    {
        return count($routeParams);
    }

    /**
     * Check and throw an exception for insufficient arguments provided for a route.
     *
     * This method checks if the number of arguments provided in the `$params` array matches the number of
     * parameters necessary for the route defined by `$routeParams`. If the counts do not match, it throws
     * an `InsufficientArgumentsForTheRoute` exception and registers it with the logger.
     *
     * @param array $params An array of parameters provided for the route.
     * @param array $routeParams An array of parameters necessary for the route.
     *
     * @throws InsufficientArgumentsForTheRoute If the number of provided arguments is insufficient.
     */
    private function throwInsufficientArgumentsForTheRoute(array $params, array $routeParams): void
    {
        $paramsInArgument = $this->countParamsInArgument($params);
        $paramsNecessary = $this->countParametersNecessaryForTheRoute($routeParams);
        if ($paramsInArgument != $paramsNecessary) {
            $exception = new InsufficientArgumentsForTheRoute("{$paramsInArgument} arguments were provided, but the route expects {$paramsNecessary}");
            Logger::getInstance()->register($exception);
            throw $exception;
        }
    }

    /**
     * Generate a route URL by replacing placeholders with provided parameters.
     *
     * This method generates a route URL by replacing placeholders within the route path with parameters
     * provided in the `$params` array. It iterates through the route path segments, identifies placeholders,
     * and substitutes them with the corresponding values from the `$params` array.
     *
     * @param array $partsOfRoute An array of route path segments.
     * @param array $params An array of parameters to replace placeholders in the route.
     *
     * @return string The route URL with placeholders replaced by the provided parameters.
     */
    private function getRouteWithParams(array $partsOfRoute, array $params): string
    {
        $count = 0;
        foreach ($partsOfRoute as $key => $part) {
            if (!empty($part) && preg_match('/\{(\w+)\}/', $part, $matches)) {
                $partsOfRoute[$key] = $params[$count];
                $count++;
            }
        }
        return implode('/', $partsOfRoute);
    }

    /**
     * Get an array of route parameters without associative keys.
     *
     * @param array $params An array of route parameters.
     *
     * @return array An indexed array of route parameters.
     */
    public function getParamsWithoutAssocKey(array $params): array
    {
        return array_values($params);
    }

    /**
     * Get all registered named routes.
     *
     * @return array An array of registered named routes.
     */
    public function getNamedRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get the URL of a named route with optional parameters.
     *
     * @throws UndefinedNamedRoute
     * @throws InsufficientArgumentsForTheRoute
     *
     * @param string $name The name of the named route.
     * @param array $params An array of parameters to replace placeholders in the route.
     *
     * @return string The URL of the named route with optional parameters.
     */
    public function getNamedRoute(string $name, array $params):string
    {
        $this->throwUndefinedNamedRoute($name);
        $partsOfRoute = $this->getPartsOfRoute($name);
        $routeParams = $this->getRouteParams($partsOfRoute);
        $this->throwInsufficientArgumentsForTheRoute($params, $routeParams);
        return $this->getRouteWithParams($partsOfRoute, $this->getParamsWithoutAssocKey($params));
    }

}