<?php

namespace Venancio\Router\Core;

class MapRoutes
{
    private array $routes;

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoutesByMethod(string $method):array
    {
        return $this->getRoutes()[$method];
    }

    public function setRoute(string $method, string $route, array $action): void
    {
        $this->routes[$method][$route] = $action;
    }


}