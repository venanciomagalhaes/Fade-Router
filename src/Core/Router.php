<?php

namespace Venancio\Router\Core;

use Venancio\Router\Exceptions\FallBackInternalServerErrorControllerUndefined;
use Venancio\Router\Exceptions\FallBackInternalServerErrorMethodUndefined;
use Venancio\Router\Exceptions\FallBackNotFoundControllerUndefined;
use Venancio\Router\Exceptions\FallBackNotFoundMethodUndefined;

class Router
{
    private string $requestMethod;
    private string $requestUri;
    private MapRoutes $mapRoutes;

    private ?string $fallBackNotFoundController = null;
    private ?string $fallBackNotFoundMethod = null;

    private ?string $fallBackInternalServerErrorController = null;
    private ?string $fallBackInternalServerErrorMethod = null;

    public function __construct()
    {
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->mapRoutes = new MapRoutes();
    }

    public function get(string $route, array $action): self
    {
        $this->mapRoutes->setRoute('GET', $route, $action);
        return $this;
    }

    public function post(string $route, array $action):self
    {
        $this->mapRoutes->setRoute('POST', $route, $action);
        return $this;
    }

    public function put(string $route, array $action):self
    {
        $this->mapRoutes->setRoute('PUT', $route, $action);
        return $this;
    }

    public function delete(string $route, array $action):self
    {
        $this->mapRoutes->setRoute('DELETE', $route, $action);
        return $this;
    }

    public function fallbackNotFound(string $controller, string $method): void
    {
        $this->fallBackNotFoundController = $controller;
        $this->fallBackNotFoundMethod = $method;
    }

    public function fallbackInternalServerError(string $controller, string $method): void
    {
        $this->fallBackInternalServerErrorController = $controller;
        $this->fallBackInternalServerErrorMethod = $method;
    }


    public function isValidRoute(): bool
    {
        return array_key_exists($this->requestUri,  $this->mapRoutes->getRoutesByMethod($this->requestMethod));
    }

    public function verifyFallBackDefined(): void
    {
        $this->verifyProperty($this->fallBackNotFoundController, FallBackNotFoundControllerUndefined::class);
        $this->verifyProperty($this->fallBackNotFoundMethod, FallBackNotFoundMethodUndefined::class);
        $this->verifyProperty($this->fallBackInternalServerErrorController, FallBackInternalServerErrorControllerUndefined::class);
        $this->verifyProperty($this->fallBackInternalServerErrorMethod, FallBackInternalServerErrorMethodUndefined::class);
    }

    private function verifyProperty($property, $exceptionClass): void
    {
        if (empty($property)) {
            throw new $exceptionClass();
        }
    }

    private function execFallBackInternalServerError(\Throwable $throwable): void
    {
        call_user_func_array([new ( $this->fallBackInternalServerErrorController),  $this->fallBackInternalServerErrorMethod], [$throwable]);
    }

    private function exec(): void
    {
       try{
           foreach ($this->mapRoutes->getRoutesByMethod($this->requestMethod) as $route => $action) {
               if ($this->requestUri == $route) {
                   $controller = $action[0];
                   $method =  $action[1];
                   call_user_func_array([new ($controller), $method], []);
               }
           }
       }catch (\Throwable $throwable){
           $this->execFallBackInternalServerError($throwable);
       }
    }

    private function execFallBackNotFound(): void
    {
        call_user_func_array([new ($this->fallBackNotFoundController),  $this->fallBackNotFoundMethod], []);
    }

    public function dispatch(): void
    {
        $this->verifyFallBackDefined();
        $this->isValidRoute() ?  $this->exec() :  $this->execFallBackNotFound() ;
    }

    public function teste()
    {
        var_dump($this->mapRoutes->getRoutesByMethod($this->requestMethod));
    }

}