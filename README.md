
## Fade\Router (English) 
Para ler em português, acesse: [README-PTBR](https://github.com/venanciomagalhaes/Fade-Router/blob/main/READEME-PTBR.md)

**Fade\Router** is an object-oriented routing system developed in PHP 8, designed to be a versatile solution for the entire PHP community. Our goal is to simplify the process of setting up a PHP application, eliminating the need to create complex routing systems based on arrays for each new project.

### Objective

Instead of wasting time and energy creating a routing system from scratch for each project, **Fade\Router** is designed to make the routing process for a PHP application simple, fast, and robust.

### Key Features

With this package, you can:

1. **Separate Routes by HTTP Methods:** Organize your routes according to major HTTP methods, such as GET, PUT, POST, and DELETE.

2. **Name Routes:** Assign meaningful names to your routes for easy reference in your code and easy retrieval in your views.

3. **Use Middlewares:** Implement middlewares to add intermediate functionality to your routes.

4. **Create Route Groups:** Group routes to:

   - Prefix route URLs.
   - Prefix route names.
   - Apply middlewares to a group of routes.

5. **Manage Error Routes:** Set separate and easily configurable actions to handle Not Found (404) and Internal Server Error (500) errors.

6. **Exception Logging:** Log all exceptions thrown during routing or unhandled by controllers, aiding in debugging and error monitoring.

### Documentation

#### Initial Setup

**Fade\Router** is a simple and straightforward routing system. First, install the package using `composer require fade\router`. Then, create an .htaccess file with these directives:

```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
```

Afterward, instantiate a **Router** object and define your routes.

#### Generic Usage Example

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

#### Creating Your First Route

To create the first route in your application with **Fade\Router**, instantiate a **Venancio\Fade\Core\Router** object and then specify the HTTP method to be used, followed by the URI and the action. The action should be an array with the controller class in the first index and the controller method to be executed in the second index.

#### Dynamic Parameter Routes

You can work with dynamic routes using **Fade\Router** by indicating dynamic parameters using the `{param}` syntax. These dynamic parameters can be retrieved in your controller by specifying the expected parameters in your method.

#### Named Routes

When defining routes with **Fade\Router**, you can provide a specific name for the route, which can be used later for reference throughout your application. You can access a named route by using the **Venancio\Fade\Core\Router::getNamedRoute()** method.

#### PUT and DELETE Routes

Browsers do not natively support the PUT and DELETE HTTP methods. To use these methods with **Fade\Router**, include a hidden input field named "_method" with the respective HTTP method (PUT or DELETE) in your POST form.

#### Middlewares

Middleware is an intermediate layer of software that operates between the client's request and the server's response. Middleware is used to process and mediate HTTP requests by performing specific actions or checks before the requests reach your application's controllers.

To use middleware in a route with **Fade\Router**, use the **middleware()** method by providing an array of middleware classes. Middleware classes must implement the **Venancio\Fade\Core\Interfaces\Middleware** interface and the **Venancio\Fade\Core\Traits\ParamsMiddleware** trait.

#### Route Grouping

Often, you want a group of routes to share the same configuration, such as a route prefix, route name prefix, or common middlewares. In **Fade\Router**, you can easily define a route group that shares specific configuration settings.

#### Error Routes (404 and 500)

**Fade\Router** provides special handling for 404 (Not Found) and 500 (Internal Server Error) errors. You can set actions to handle these errors by using the **fallbackNotFound()** and **fallbackInternalServerError()** methods.

#### Logging

Exceptions related to **Fade\Router** configurations and other unhandled exceptions in your application are logged in the `logs/fade/router.log` file, which can be helpful for debugging your application.

#### Tests

**Fade\Router** includes more than 38 tests that can be executed with the command `vendor/bin/phpunit tests/ --testdox --colors`.

## Contributions

We appreciate contributions from any member of the PHP community interested in improving **Fade\Router**. Feel free to open issues or submit pull requests on our GitHub repository.

## License

This package is licensed under the [MIT License](LINK_TO_LICENSE). Please refer to the LICENSE file for details.

## Authors

Developed with passion by [Venâncio Magalhães](https://www.linkedin.com/in/deividsonvm/).

Questions or Suggestions? Contact us.
