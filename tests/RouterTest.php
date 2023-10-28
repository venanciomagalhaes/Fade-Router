<?php

namespace Venancio\Fade\Tests;

use PHPUnit\Framework\TestCase;
use Venancio\Fade\Core\Router;
use Venancio\Fade\Exceptions\DuplicateNamedRoute;
use Venancio\Fade\Exceptions\FallbackInternalServerErrorControllerUndefined;
use Venancio\Fade\Exceptions\FallbackInternalServerErrorMethodUndefined;
use Venancio\Fade\Exceptions\FallbackNotFoundControllerUndefined;
use Venancio\Fade\Exceptions\FallbackNotFoundMethodUndefined;
use Venancio\Fade\Exceptions\InsufficientArgumentsForTheRoute;
use Venancio\Fade\Exceptions\UndefinedNamedRoute;
use Venancio\Fade\Tests\Controllers\General;
use Venancio\Fade\Tests\Controllers\InternalServerError;
use Venancio\Fade\Tests\Controllers\NotFound;

class RouterTest extends TestCase
{
    const SUCCESS = '200';
    const NOT_FOUND = '400';
    const INTERNAL_SERVER_ERROR = '500';
    public Router $router;

    public function setUp(): void
    {
        unset($_SERVER);
        unset($_POST);
        $_SERVER['REQUEST_METHOD'] = '';
        $_SERVER['REQUEST_URI'] = '';
        $this->router = new Router();
    }

    /**
     * @test
     */
    public function defineRouteGet(): void
    {
        $route = '/home';
        $this->router->get($route, []);
        $hasRoute = array_key_exists($route, $this->router->getRoutes()['GET']);
        $this->assertTrue($hasRoute, 'Unable to set GET route');
    }

    /**
     * @test
     */
    public function defineRoutePost(): void
    {
        $route = '/blog';
        $this->router->post($route, []);
        $hasRoute = array_key_exists($route, $this->router->getRoutes()['POST']);
        $this->assertTrue($hasRoute, 'Unable to set POST route');
    }

    /**
     * @test
     */
    public function defineRoutePut(): void
    {
        $route = '/blog/1';
        $this->router->put($route, []);
        $hasRoute = array_key_exists($route, $this->router->getRoutes()['PUT']);
        $this->assertTrue($hasRoute, 'Unable to set PUT route');
    }

    /**
     * @test
     */
    public function defineRouteDelete(): void
    {
        $route = '/blog/1';
        $this->router->delete($route, []);
        $hasRoute = array_key_exists($route, $this->router->getRoutes()['DELETE']);
        $this->assertTrue($hasRoute, 'Unable to set DELETE route');
    }

    /**
     * @test
     */
    public function checkActionInGetRoute()
    {
        $_SERVER['REQUEST_URI'] = '/home';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $router = new Router();
        $router->get('/home', [General::class, 'index']);
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->fallbackInternalServerError(InternalServerError::class, 'report');
        $this->assertEquals(
            self::SUCCESS,
            $router->dispatch(),
            'Unable to activate the controller on the GET route'
        );
    }

    /**
     * @test
     */
    public function checkActionInPostRoute()
    {
        $_SERVER['REQUEST_URI'] = '/history';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $router = new Router();
        $router->post('/history', [General::class, 'store']);
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->fallbackInternalServerError(InternalServerError::class, 'report');
        $this->assertEquals(
            self::SUCCESS,
            $router->dispatch(),
            'Unable to activate the controller on the POST route'
        );
    }

    /**
     * @test
     */
    public function checkActionInPutRoute()
    {
        $_SERVER['REQUEST_URI'] = '/blog/22';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method'] = 'PUT';
        $router = new Router();
        $router->put('/blog/22', [General::class, 'update']);
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->fallbackInternalServerError(InternalServerError::class, 'report');
        $this->assertEquals(
            self::SUCCESS,
            $router->dispatch(),
            'Unable to activate the controller on the GET route'
        );
    }

    /**
     * @test
     */
    public function checkActionInDeleteRoute()
    {
        $_SERVER['REQUEST_URI'] = '/blog/22';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method'] = 'DELETE';
        $router = new Router();
        $router->delete('/blog/22', [General::class, 'destroy']);
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->fallbackInternalServerError(InternalServerError::class, 'report');
        $this->assertEquals(
            self::SUCCESS,
            $router->dispatch(),
            'Unable to activate the controller on the DELETE route'
        );
    }

    /**
     * @test
     */
    public function checkExceptionThrowWhenFallbackControllerIsMissing()
    {
        $_SERVER['REQUEST_URI'] = '/blog/22';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method'] = 'DELETE';
        $this->expectException(FallbackNotFoundControllerUndefined::class);
        $router = new Router();
        $router->delete('/blog/22', [General::class, 'destroy']);
        $router->dispatch();
    }

    /**
     * @test
     */
    public function checkExceptionThrowWhenFallbackMethodIsMissing()
    {
        $_SERVER['REQUEST_URI'] = '/blog/22';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method'] = 'DELETE';
        $this->expectException(FallbackNotFoundMethodUndefined::class);
        $router = new Router();
        $router->fallbackNotFound(NotFound::class, '') ;
        $router->delete('/blog/22', [General::class, 'destroy']);
        $router->dispatch();
    }

    /**
     * @test
     */
    public function checkExceptionThrowWhenFallbackInternalServerErrorControllerIsMissing()
    {
        $_SERVER['REQUEST_URI'] = '/blog/22';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method'] = 'DELETE';
        $this->expectException(FallbackInternalServerErrorControllerUndefined::class);
        $router = new Router();
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->delete('/blog/22', [General::class, 'destroy']);
        $router->dispatch();
    }

    /**
     * @test
     */
    public function checkExceptionThrowWhenFallbackInternalServerErrorMethodIsMissing()
    {
        $_SERVER['REQUEST_URI'] = '/blog/22';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method'] = 'DELETE';
        $this->expectException(FallbackInternalServerErrorMethodUndefined::class);
        $router = new Router();
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->fallbackInternalServerError(InternalServerError::class, '');
        $router->delete('/blog/22', [General::class, 'destroy']);
        $router->dispatch();
    }

    /**
     * @test
     */
    public function defineNamedRouteGet(): void
    {
        $route = '/home';
        $this->router->get($route, [])->name('home.index');
        $this->assertEquals(
            $route,
            Router::getNamedRoute('home.index'),
            'Unable to set GET named route'
        );
    }

    /**
     * @test
     */
    public function defineNamedRoutePost(): void
    {
        $route = '/blog';
        $this->router->post($route, [])->name('blog.create');
        $this->assertEquals(
            $route,
            Router::getNamedRoute('blog.create'),
            'Unable to set POST named route'
        );
    }

    /**
     * @test
     */
    public function defineNamedRoutePut(): void
    {
        $route = '/blog/1';
        $this->router->put($route, [])->name('blog.update');
        $this->assertEquals(
            $route,
            Router::getNamedRoute('blog.update'),
            'Unable to set PUT named route'
        );
    }

    /**
     * @test
     */
    public function defineNamedRouteDelete(): void
    {
        $route = '/blog/1';
        $this->router->delete($route, [])->name('blog.destroy');
        $this->assertEquals(
            $route,
            Router::getNamedRoute('blog.destroy'),
            'Unable to set DELETE named route'
        );
    }

    /**
     * @test
     */
    public function defineGetNamedRouteWithDynamicParameter()
    {
        $route = "contact/{theme}";
        $dynamicParameter = 1;
        $routeExpected = "contact/{$dynamicParameter}";
        $this->router->get($route, [])->name('contact.theme');
        $this->assertEquals(
            $routeExpected,
            Router::getNamedRoute('contact.theme', [$dynamicParameter]),
        'Unable to set GET named route with dynamic parameter'
        );
    }

    /**
     * @test
     */
    public function definePostNamedRouteWithDynamicParameter()
    {
        $route = "contact/{theme}";
        $dynamicParameter = 1;
        $routeExpected = "contact/{$dynamicParameter}";
        $this->router->post($route, [])->name('contact.theme');
        $this->assertEquals(
            $routeExpected,
            Router::getNamedRoute('contact.theme', [$dynamicParameter]),
        'Unable to set POST named route with dynamic parameter'
        );
    }

    /**
     * @test
     */
    public function definePutNamedRouteWithDynamicParameter()
    {
        $route = "contact/{theme}";
        $dynamicParameter = 5;
        $routeExpected = "contact/{$dynamicParameter}";
        $this->router->put($route, [])->name('contact.theme.update');
        $this->assertEquals(
            $routeExpected,
            Router::getNamedRoute('contact.theme.update', [$dynamicParameter]),
        'Unable to set PUT named route with dynamic parameter'
        );
    }

    /**
     * @test
     */
    public function defineDeleteNamedRouteWithDynamicParameter()
    {
        $route = "contact/{theme}";
        $dynamicParameter = 5;
        $routeExpected = "contact/{$dynamicParameter}";
        $this->router->delete($route, [])->name('contact.theme.delete');
        $this->assertEquals(
            $routeExpected,
            Router::getNamedRoute('contact.theme.delete', [$dynamicParameter]),
        'Unable to set DELETE named route with dynamic parameter'
        );
    }


    /**
     * @test
     */
    public function defineGroupNameRoute()
    {
        $router = new Router();
        $router->group(['name' => 'group.'], function  () use($router) {
           $router->get('/example/group', [General::class, 'index'])->name('example');
        });
        $router->post('/contact', [General::class, 'store'])->name('contact.store');
        $this->assertEquals(
            '/example/group',
            Router::getNamedRoute('group.example'),
            'Unable to define named route group'
        );
        $this->assertEquals(
            '/contact',
            Router::getNamedRoute('contact.store'),
            'Error in define named route group'
        );

    }

    /**
     * @test
     */
    public function defineGroupPrefixRoute()
    {
        $router = new Router();
        $router->group(['name' => 'group.', 'prefix' => 'prefix'], function  () use($router) {
            $router->get('/example/group', [General::class, 'index'])->name('example');
        });
        $router->post('/contact', [General::class, 'store'])->name('contact.store');

        $this->assertEquals(
            'prefix/example/group',
            Router::getNamedRoute('group.example'),
            'Unable to define named route group'
        );
        $this->assertEquals(
            '/contact',
            Router::getNamedRoute('contact.store'),
            'Error in define named route group'
        );

    }

    /**
     * @test
     */
    public function checkExceptionThrownWhenRouteNameIsWrong()
    {
        $this->expectException(UndefinedNamedRoute::class);
        $router = new Router();
        $router->get('/home', [General::class, 'index'])->name('index');
        Router::getNamedRoute('indes');
    }

    /**
     * @test
     */
    public function checkExceptionThrownWhenDuplicateNameRoute()
    {
        $this->expectException(DuplicateNamedRoute::class);
        $router = new Router();
        $router->get('/home', [General::class, 'index'])->name('index');
        $router->post('/contact', [General::class, 'index'])->name('index');
    }

    /**
     * @test
     */
    public function checksForThrowingExceptionsWhenParametersAreMissingOnTheRoute()
    {
        $this->expectException(InsufficientArgumentsForTheRoute::class);
        $route = "contact/{theme}";
        $this->router->delete($route, [])->name('contact.theme.delete');
        Router::getNamedRoute('contact.theme.delete');
    }

    /**
     * @test
     */
    public function checksForThrowingExceptionsWhenParametersExceedInRoute()
    {
        $this->expectException(InsufficientArgumentsForTheRoute::class);
        $route = "contact/{theme}";
        $this->router->delete($route, [])->name('contact.theme.delete');
        Router::getNamedRoute('contact.theme.delete', [2, 5]);
    }


    /**
     * @test
     */
    public function checkFallbackRouteNotFound()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';
        $router = new Router();
        $router->get('/', [General::class, 'index']);
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->fallbackInternalServerError(InternalServerError::class, 'report');
        self::assertEquals(
            '404'
            , $router->dispatch(),
        'Error testing fallback route not found');
    }

    /**
     * @test
     */
    public function checkForcingFallbackRouteNotFound()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';
        $router = new Router();
        $router->get('/home', [General::class, 'forcingNotFound']);
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->fallbackInternalServerError(InternalServerError::class, 'report');
        self::assertEquals(
            '404'
            , $router->dispatch(),
            'Error testing fallback route not found');
    }

    /**
     * @test
     */
    public function checkFallbackRouteInternalServerError()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';
        $router = new Router();
        $router->get('/home', [General::class, 'internalServerError']);
        $router->fallbackNotFound(NotFound::class, 'report');
        $router->fallbackInternalServerError(InternalServerError::class, 'report');
        self::assertEquals(
            '500'
            , $router->dispatch(),
            'Error testing fallback route not found');
    }

}