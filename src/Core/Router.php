<?php

namespace Venancio\Fade\Core;

use Venancio\Fade\Core\Interfaces\Middleware;
use Venancio\Fade\Exceptions\FallbackInternalServerErrorControllerUndefined;
use Venancio\Fade\Exceptions\FallbackInternalServerErrorMethodUndefined;
use Venancio\Fade\Exceptions\FallbackNotFoundControllerUndefined;
use Venancio\Fade\Exceptions\FallbackNotFoundMethodUndefined;
use Venancio\Fade\Exceptions\NotFound;

final class Router
{
    private string $requestMethod;
    private string $requestUri;

    private array $paramsURI = [];
    private static MapRoutes $mapRoutes;

    private ?string $fallBackNotFoundController = null;
    private ?string $fallBackNotFoundMethod = null;

    private ?string $fallBackInternalServerErrorController = null;
    private ?string $fallBackInternalServerErrorMethod = null;

    public function __construct()
    {
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $this->requestMethod = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        self::$mapRoutes = new MapRoutes();
    }

    public function get(string $route, array $action): self
    {
        self::$mapRoutes->setRoute('GET', $route, $action);
        return $this;
    }

    public function post(string $route, array $action):self
    {
        self::$mapRoutes->setRoute('POST', $route, $action);
        return $this;
    }

    public function put(string $route, array $action):self
    {
        self::$mapRoutes->setRoute('PUT', $route, $action);
        return $this;
    }

    public function delete(string $route, array $action):self
    {
        self::$mapRoutes->setRoute('DELETE', $route, $action);
        return $this;
    }

    private function setGroupName(array $options): void
    {
        if(isset($options['name'])){
            self::$mapRoutes->setGroupName($options['name']);
        }
    }

    private function setGroupPrefix(array $options): void
    {
        if(isset($options['prefix'])){
            self::$mapRoutes->setGroupPrefix($options['prefix']);
        }
    }
    private function clearGroup(): void
    {
        self::$mapRoutes->setGroupMiddleware(null);
        self::$mapRoutes->setGroupPrefix(null);
        self::$mapRoutes->setGroupName(null);
    }

    private function setGroupMiddleware(array $options): void
    {
        if(isset($options['middleware'])){
            self::$mapRoutes->setGroupMiddleware($options['middleware']);
        }
    }

    public function group(array $options, \Closure $callback): void
    {
        $this->setGroupMiddleware($options);
        $this->setGroupPrefix($options);
        $this->setGroupName($options);
        $callback();
        $this->clearGroup();
    }

    public function name(string $name): void
    {
        self::$mapRoutes->setNamedRoute($name);
    }

    public function middleware(array $middlewares):self
    {
        self::$mapRoutes->setSingleMiddleware($middlewares);
        return $this;
    }

    public static function getNamedRoute(string $name, array $params = []): string
    {
       return self::$mapRoutes->getNamedRoute($name, $params);
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

    private function decomposeCurrentRequestUri(): array
    {
        return explode('/', trim( $this->requestUri, '/'));
    }

    private function decomposePatternUri(string $patternUri): array
    {
        return explode('/', trim($patternUri, '/'));
    }

    private function notIsCompatibleUriAndPattern(array $uriParts, array $patternParts): bool
    {
        return count($uriParts) !== count($patternParts);
    }

    private function isUriPartEqualsUriPatternsPart($patternParts, $uriParts): bool
    {
        return $patternParts === $uriParts;
    }

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

    private function getParamsToControllers(): array
    {
        $request = $_POST ?? $_GET;
        $request['FILES'] = $_FILES;
        return [$request, ...$this->paramsURI];
    }

    private function hasMiddleware(mixed $action): bool
    {
        return array_key_exists('middlewares', $action);
    }

    private function isValidMiddleware(mixed $middleware): bool
    {
        return $middleware instanceof Middleware;
    }

    private function dispatchMiddleware($middlewares1): void
    {
        $middlewares = $middlewares1;
        foreach ($middlewares as $middleware) {
            $middleware = (new $middleware);
            if ($this->isValidMiddleware($middleware)) {
                $middleware->handle();
            }
        }
    }

    private function execMiddlewares(mixed $action): void
    {
        if ($this->hasMiddleware($action)) {
            $this->dispatchMiddleware($action['middlewares']);
        }
    }
    
    private function execAction(mixed $action): void
    {
        $controller = $action[0];
        $method = $action[1];
        call_user_func_array([new ($controller), $method], $this->getParamsToControllers());
    }

    private function isCurrentURI(int|string $route): bool
    {
        return $this->requestUri == $route;
    }

    private function exec(): string
    {
       try{
           foreach (self::$mapRoutes->getRoutesByMethod($this->requestMethod) as $route => $action) {
               if ($this->isCurrentURI($route)) {
                   $this->execMiddlewares($action);
                   $this->execAction($action);
                   return '200';
               }
           }
       } catch (NotFound $exception) {
           $this->execFallBackNotFound();
           return '404';
       } catch (\Throwable $throwable){
           $this->execFallBackInternalServerError($throwable);
           return '500';
       }
    }

    private function execFallBackNotFound(): string
    {
        call_user_func_array([new ($this->fallBackNotFoundController),  $this->fallBackNotFoundMethod], []);
        return '404';
    }

    private function finally(): string
    {
        return $this->isValidRoute() ? $this->exec() : $this->execFallBackNotFound();
    }

    public function dispatch(): ?string
    {
        $this->verifyFallBackDefined();
        return $this->finally();
    }

    public function getRoutes(): array
    {
        return self::$mapRoutes->getRoutes();
    }

}