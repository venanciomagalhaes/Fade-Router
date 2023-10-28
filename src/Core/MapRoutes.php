<?php

namespace Venancio\Fade\Core;

use Venancio\Fade\Exceptions\InsufficientArgumentsForTheRoute;
use Venancio\Fade\Exceptions\UndefinedNamedRoute;

final class  MapRoutes
{
    private array $routes = [];

    private ?array $groupMiddleware = [];

    private NamedRoutes $namedRoutes;

    private ?string $groupName = null;
    private ?string $groupPrefix = null;

    private string $lastRouteRegistered;

    public function __construct()
    {
        $this->namedRoutes = new NamedRoutes();
    }


    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoutesByMethod(string $method): ?array
    {
        return $this->getRoutes()[$method];
    }

    private function getRouteWithPrefix(string $route): string
    {
        return $this->groupPrefix ? "{$this->groupPrefix}{$route}" : $route;
    }

    public function setRoute(string $method, string $route, array $action): void
    {
        $this->routes[$method][$this->getRouteWithPrefix($route)] = $action;

        $this->setLastRouteRegistered($route);
    }

    public function setLastRouteRegistered(string $lastRouteRegistered): void
    {
        $this->lastRouteRegistered = $lastRouteRegistered;
    }

    public function getNamedRoutes(): array
    {
        return $this->namedRoutes->getNamedRoutes();
    }

    public function getNamedRoute(string $name, $params = []): string
    {
      return $this->namedRoutes->getNamedRoute($name, $params);
    }

    public function setNamedRoute($name): void
    {
        $name =  $this->groupName ? "{$this->groupName}{$name}" : $name;
        $lastRoute = $this->groupPrefix ? "{$this->groupPrefix}{$this->lastRouteRegistered}": $this->lastRouteRegistered;
        $this->namedRoutes->setRoute($name,$lastRoute);
    }

    public function setGroupMiddleware(?array $middlewares): void
    {
        if(is_array($middlewares)){
            foreach ($middlewares as $middleware){
                $this->groupMiddleware[] = $middleware;
            }
        }
    }

    public function setGroupName(?string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function setGroupPrefix(?string $groupPrefix): void
    {
        $this->groupPrefix = $groupPrefix;
    }



}