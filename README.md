
# Fade\Router (English) 
``composer require fade/router``

Para ler em português, acesse: [README-PTBR](https://github.com/venanciomagalhaes/Fade-Router/blob/main/READEME-PTBR.md)

Fade\Router is an object-oriented routing system developed in PHP 7.4, created with the purpose of being a versatile solution for the entire PHP community. Our goal is to simplify the initialization process of a PHP application, eliminating the need to create complex routing systems based on arrays for each new application.

## Objective

Instead of wasting time and energy creating a routing system from scratch for each project, Fade\Router is designed to make the routing process of a PHP application simple, fast, and robust.

## Key Features

With this package, you can:

1. **Separate Routes by HTTP Methods:** Organize your routes according to the main HTTP methods such as GET, PUT, POST, and DELETE.

2. **Name Routes:** Assign meaningful names to your routes for easy reference in your code and easy retrieval in your views.

3. **Use Middlewares:** Implement middlewares to add intermediate functionality to your routes.

4. **Create Route Groups:** Group routes to:

   4.1. Prefix route URLs.

   4.2. Prefix route names.

   4.3. Apply middlewares to a group of routes.

5. **Manage Error Routes:** Define separate and easily configurable actions to handle Not Found (404) and Internal Server Error (500) errors.

6. **Exception Logging:** Record all exceptions thrown during routing or not handled by controllers, helping in debugging and error monitoring.

## Documentation

### Initial Configuration

Fade\Router is a simple and direct routing system, as seen below. First, you need to install the package using the command `composer require fade\router`. Then, create a .htaccess file with the following directives:

```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
```

After that, simply instantiate an object of the Router type and define your routes with their respective configurations.

### Generic Example of Using Fade\Router

```php
<?php

use Venancio\Fade\Core\Router;
use MyApplication\Controllers\HomeController;
use MyApplication\Controllers\UserController;
use MyApplication\Controllers\NotFound;
use MyApplication\Controllers\InternalServerError;
use MyApplication\Middlewares\AdminMiddleware;
use MyApplication\Middlewares\EspecialUserMiddleware;

$router = new Router();
$router->get('/', [HomeController::class, 'index'])->name('home.index');

// Single Middleware
$router->middleware([EspecialUserMiddleware::class])->post('/user', [UserController::class, 'store'])->name('user.store');

// Route Groups
$router->group(['prefix' => 'admin', 'name' => 'admin.', 'middleware' => [AdminMiddleware::class]], function () use ($router){
        $router->put('/user/{id}', [UserController::class, 'update'])->name('user.update');
        $router->delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
});

// Fallback Actions
$router->fallbackNotFound(NotFound::class, 'report');
$router->fallbackInternalServerError(InternalServerError::class, 'report');

$router->dispatch();
```

### Creating the First Route

To create the first route for your application with Fade\Router, simply instantiate an object of the `Venancio\Fade\Core\Router` type and then specify the HTTP method to be used, followed by the URI and the action, which is an array with the controller class in the first index and the controller method in the second index to be executed.

```php
$router = new Router();
$router->get('/', [HomeController::class, 'index']);
```

As mentioned earlier, Fade\Router provides special handling for 404 and 500 errors. Therefore, before dispatching the router, it is necessary to define the two fallback routes. In each method, the controller class followed by the method to be executed is expected. This allows you to define, for example, a view or a specific action for 400 or 500 errors.

```php
$router->fallbackNotFound(NotFound::class, 'report'); // 400
$router->fallbackInternalServerError(InternalServerError::class, 'report'); // 500
```

Then, simply call the `dispatch()` method to start routing the application.

```php
$router->dispatch();
```

Failure to define the fallback actions will throw exceptions of the following types:

1.  ```Venancio\Fade\Core\Exceptions\FallbackNotFoundControllerUndefined```
2. ```Venancio\Fade\Core\Exceptions\FallbackNotFoundMethodUndefined```
3. ```Venancio\Fade\Core\Exceptions\FallbackInternalServerErrorControllerUndefined```
4. ```Venancio\Fade\Core\Exceptions\FallbackInternalServerErrorMethodUndefined```.

### Routes with Dynamic Parameters

You can work with dynamic routes using Fade\Router by indicating the dynamic parameter using the syntax `{param}`:

```php
$router->get('/user/{id}', [UserController::class, 'show']);
```

To dynamically receive this parameter in the route's controller, specify in your method that it will receive a parameter:

```php
class UserController
{
    public function show($id): void
    {
        echo $id;
    }
}
```

If you have more than one dynamic parameter, simply provide the same number of parameters in the controller for reception.

### Working with Named Routes

During the creation of our routes, you can define a specific name for the route, which can be used later in the application for reference. **The `name()` method should always be the last in the method chaining**.

```php
$router = new Router();
$router->get('/user/{id}', [UserController::class, 'show'])->name('user.show');
```

After defining it, this route can be called anywhere in the application using the `Venancio\Fade\Core\Router` class itself through the static method `getNamedRoute()`. This method expects two parameters (the last one is optional): the route name and, when needed, an array with

 the required route parameters:

```php
<a href="<?= \Venancio\Fade\Core\Router::getNamedRoute('user.show', [$idUser]) ?>"/>
```

Attempting to assign the same name to two routes will throw an exception of the type `Venancio\Fade\Core\Exceptions\DuplicateNamedRoute`, while attempting to access a route using a nonexistent name will throw an exception of the type `Venancio\Fade\Core\Exceptions\UndefinedNamedRoute`.

Attempting to pass more or fewer parameters than necessary for a named route will throw an exception of the type `Venancio\Fade\Core\Exceptions\InsufficientArgumentsForTheRoute`.

### PUT and DELETE Routes

Since browsers natively do not support the use of PUT and DELETE methods, for proper routing, whenever you want to send a request as PUT or DELETE, you need to provide a POST method form with a hidden input named _method with the HTTP method type.

To simplify this, Fade\Router provides static methods in its class that handle this. Just invoke each one, respectively, `methodPUT()` and `methodDELETE()`:

```php
<form method="POST" action="<?= \Venancio\Fade\Core\Router::getNamedRoute('admin.user.update', [$idUser]) ?>">
    <?= \Venancio\Fade\Core\Router::methodPUT() ?>
</form>
```

```php
<form method="POST" action="<?= \Venancio\Fade\Core\Router::getNamedRoute('admin.user.destroy', [$idUser]) ?>">
    <?= \Venancio\Fade\Core\Router::methodDELETE() ?>
    <input type="submit" value="DELETE">
</form>
```

### Middlewares

Middleware is an intermediate layer of software that acts between the client's request and the server's response. The main purpose of middleware is to process and mediate HTTP requests, performing specific actions or checks before these requests reach the application's controllers. Some common examples of tasks performed by middlewares include user authentication, authorization, logging, cookie handling, exception handling, among others.

To use middlewares on a route with Fade\Router, it is straightforward: just use the `middleware()` method, passing an array of middleware classes as a parameter. **The `middleware` method should always be the first in the method chaining**.

```php
$router->middleware([AuthMiddleware::class, EspecialUserMiddleware::class])->post('/user', [UserController::class, 'store'])->name('user.store');
```

Middleware classes must implement the `Venancio\Fade\Core\Interfaces\Middleware` interface and use the `Venancio\Fade\Core\Traits\ParamsMiddleware` trait. If the implementation is not done, an exception of type `Venancio\Fade\Core\Exceptions\InvalidTypeMiddleware` will be thrown.

The logic of the middleware should be called in the `handle()` method of your class. **By default, all middlewares pass the request forward unless you do something to prevent it**. In other words, to prevent the request from reaching the controller, you should implement your own logic from the `handle` method, as in the example below:

```php
class AuthMiddleware implements Middleware
{
    use ParamsMiddleware;

    public function handle(): void
    {
        // Insert authentication logic here
        if (!$auth) {
            header('Location: /login');
        }
    }
}
```

In addition, in routes with dynamic parameters, these parameters can be retrieved within the middleware using the `$this->params` property, which will return an array with the dynamic parameters in the order they were provided.

### Defining a Route Group

Often, you may want a set of routes to share the same configuration, such as a route prefix, a route name prefix, or even common middlewares. With Fade\Router, you can easily define a route group that will share a specific configuration. To do this, simply call the `group()` method, which expects two parameters: an associative array of configuration - which can have keys like `prefix` (for route prefix), `name` (for route name prefix), and `middleware` (to share a common middleware) - and an anonymous function where the group's routes can be defined individually:

```php
$router->group(['prefix' => 'admin', 'name' => 'admin.', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function () use ($router){
        $router->put('/user/{id}', [UserController::class, 'update'])->name('user.update');  // route: PUT admin/user/{id} | name: admin.user.update
        $router->delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy'); // route: DELETE admin/user/{id} | name: admin.user.destroy
});
```

Within a group, you can define other groups, just as you can define individual middlewares for routes within the group.

### Error Handling Routes (404 and 500)

As mentioned earlier, Fade\Router provides special handling for 404 (Not Found) and 500 (Internal Server Error) errors. Therefore, before dispatching the router, you need to define the two fallback routes. In each method, you should specify the controller class followed by the method to be executed in case of an error. This allows you to define, for example, a view or a specific action for 400 or 500 errors.

```php
$router->fallbackNotFound(NotFound::class, 'report'); // 400
$router->fallbackInternalServerError(InternalServerError::class, 'report'); // 500
```

In addition, Fade\Router has special handling that allows a user to be easily redirected to the 404 (Not Found) action. To do this, in your application, throw an unhandled exception of type `Venancio\Fade\Core\Exceptions\NotFound`, and then the `fallbackNotFound` action will be triggered.

### Logs

Any exceptions related to the Fade\Router settings, as well as other unhandled exceptions in the application, are logged in `logs/fade/router.log`. If you have any doubts, checking the log will be a good starting point for debugging the application.

### Tests

Fade\Router has more than 38 tests that can be verified and followed by running `composer require phpunit/phpunit --dev` and `vendor/bin/phpunit vendor/fade/router/tests/ --testdox --colors`.

## Contributions

We appreciate contributions from any PHP community members interested in improving Fade\Router. Feel free to open issues or send pull requests to our GitHub repository.

## License

This package is licensed under the [MIT](https://github.com/venanciomagalhaes/Fade-Router/blob/main/LICENSE) License. See the LICENSE file for details.

## Authors

Developed with passion by [Venâncio Magalhães](https://www.linkedin.com/in/deividsonvm/).

Questions or Suggestions? Contact us.
