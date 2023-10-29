<?php

namespace Venancio\Fade\Core;

use Venancio\Fade\Core\Exceptions\DuplicateNamedRoute;
use Venancio\Fade\Core\Exceptions\InsufficientArgumentsForTheRoute;
use Venancio\Fade\Core\Exceptions\UndefinedNamedRoute;
use Venancio\Fade\Core\Log\Logger;

final class NamedRoutes
{
    private array $routes = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoute(string $name): string
    {
        return $this->routes[$name] ?? '';
    }

    private function throwDuplicateNamedRoute(string $name): void
    {
        if (key_exists($name, $this->routes)) {
            $exception = new DuplicateNamedRoute();
            Logger::getInstance()->register($exception);
            throw $exception;
        }
    }

    public function setRoute(string $name, string $route): void
    {
        $this->throwDuplicateNamedRoute($name);
        $this->routes[$name] = $route;
    }

    private function namedRouteNotExists($nameRoute): bool
    {
        return !key_exists($nameRoute, $this->getRoutes());
    }

    private function throwUndefinedNamedRoute($nameRoute): void
    {
        if($this->namedRouteNotExists($nameRoute))
            throw new UndefinedNamedRoute($nameRoute);
    }

    private function getPartsOfRoute(string $nameRoute): array
    {
        return explode('/',  $this->getRoute($nameRoute));
    }

    private function getRouteParams(array $partsOfRoute): array
    {
        return array_filter($partsOfRoute, function ($part) {
            return preg_match('/\{(\w+)\}/', $part, $matches);
        });
    }

    private function countParamsInArgument(array $params): int
    {
        return count($params);
    }

    private function countParametersNecessaryForTheRoute(array $routeParams): int
    {
        return count($routeParams);
    }

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

    public function getParamsWithoutAssocKey(array $params): array
    {
        return array_values($params);
    }

    public function getNamedRoutes(): array
    {
        return $this->routes;
    }

    public function getNamedRoute(string $name, array $params):string
    {
        $this->throwUndefinedNamedRoute($name);
        $partsOfRoute = $this->getPartsOfRoute($name);
        $routeParams = $this->getRouteParams($partsOfRoute);
        $this->throwInsufficientArgumentsForTheRoute($params, $routeParams);
        return $this->getRouteWithParams($partsOfRoute, $this->getParamsWithoutAssocKey($params));
    }

}