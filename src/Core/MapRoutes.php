<?php

namespace Venancio\Fade\Core;

use Venancio\Fade\Exceptions\InsufficientArgumentsForTheRoute;
use Venancio\Fade\Exceptions\UndefinedNamedRoute;

final class  MapRoutes
{
    private array $routes = [];

    private ?array $groupMiddleware = [];
    private ?array $singleMiddleware = [];

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

    private function defineMiddlewares(string $method, string $route): void
    {
        if ($this->groupMiddleware) {
            $this->routes[$method][$this->getRouteWithPrefix($route)]['middlewares'] = $this->groupMiddleware;
        }

        if ($this->singleMiddleware) {
            $this->routes[$method][$this->getRouteWithPrefix($route)]['middlewares'] = $this->singleMiddleware;
        }
    }

    private function clearSingleMiddleware(): void
    {
        $this->setSingleMiddleware(null);
    }

    public function setRoute(string $method, string $route, array $action): void
    {
        $this->routes[$method][$this->getRouteWithPrefix($route)] = $action;
        $this->defineMiddlewares($method, $route);
        $this->setLastRouteRegistered($route);
        $this->clearSingleMiddleware();
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

    public function setGroupName(?string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function setGroupPrefix(?string $groupPrefix): void
    {
        $this->groupPrefix = $groupPrefix;
    }

}