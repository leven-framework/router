# Leven Router

## Features
- 📝 route parameters
- 🪹 nestable route groups
- 🔧 configure routes with PHP8 attributes
- 💉 dependency injection using [Auryn](https://github.com/rdlowrey/auryn)
- 🔄 reverse routing (controller -> path)
- 🚥 middleware support (before and after)

## Basic usage example

```php
require 'vendor/autoload.php';

($injector = new \Auryn\Injector)->share( $request = \Leven\Router\Messages\Request::fromSuperglobals() );
$rc = new \Leven\Router\RouterConfigurator( $router = new \Leven\Router\Router );


$rc->post('/demo', function(\Leven\Router\Messages\Request $request) {
    return "Hello {$request->body->name}";
});

$rc->get('/test/{id}', function(\Leven\Router\RouteParams $params) {
    return "ID: {$params->id}";
});


(new \Leven\Router\RouteHandler($injector))->handle($router->match($request))->dispatch();
```

## Attribute configuration + reverse routing example

```php
require 'vendor/autoload.php';

($injector = new \Auryn\Injector)->share( $request = \Leven\Router\Messages\Request::fromSuperglobals() );
$router = new \Leven\Router\Router;


class MyController {
    #[\Leven\Router\Route('GET', '/foo')]
    public function fooController(){
        return new \Leven\Router\Messages\JsonResponse(['foo' => 'hello']);
    }
    
    #[\Leven\Router\Route('GET', '/bar')]
    public function barController(\Leven\Router\RouteUrlResolver $url){
        $href = $url([static::class, 'fooController']);
        return new \Leven\Router\Messages\HtmlResponse("<a href='$href'>foo</a>");
    }
}

(new \Leven\Router\ControllerScanner($router))
    ->scanControllerClasses(MyController::class, );
   
// RouteUrlResolver allows you to get route path of a given controller
$injector->share( new \Leven\Router\RouteUrlResolver($router) );


(new \Leven\Router\RouteHandler($injector))->handle($router->match($request))->dispatch();
```

## Middleware example

```php
require 'vendor/autoload.php';

($injector = new \Auryn\Injector)->share( $request = \Leven\Router\Messages\Request::fromSuperglobals() );
$rc = new \Leven\Router\RouterConfigurator( $router = new \Leven\Router\Router );


class MyMiddleware {
    function __invoke(\Leven\Router\MiddlewareCallback $next) {
        $response = $next();
        return "before $response->body after";
    }
}

$rc->post('/cool', fn() => "something")->middleware(MyMiddleware::class);


(new \Leven\Router\RouteHandler($injector))->handle($router->match($request))->dispatch();
```

## Installation

PHP 8.1+ is required.

```sh
composer install leven-framework/router
```

## Gotchas

- You're locked into using [Auryn](https://github.com/rdlowrey/auryn) for dependency injection
- No support for other Request or Response classes like PSR-7
- There's no production optimization for scanned routes yet implemented
- This readme is only documentation for now
- No tests written yet

## Documentation

```php
<?php

require 'vendor/autoload.php';

// first we need to create a dependency injector; check https://github.com/rdlowrey/auryn for more info
$injector = new \Auryn\Injector();

// initialize a Request object automatically from the superglobals ($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER)
$request = \Leven\Router\Messages\Request::fromSuperglobals();

// here we share the Request object with the injector, so Auryn can inject it into our controllers
$injector->share($request);

// this object will hold the route registry and perform route matching
$router = new \Leven\Router\Router;

// you can optionally specify middleware to be prepended to each route's middleware stack
$router->addGlobalMiddleware( MyMiddleware::class, function( ){ }, [MyMiddleware::class, 'someMethod'], );



// you can use the RouterConfigurator to easily add routes to the router
$config = new \Leven\Router\RouterConfigurator($router);

$config->get('/demo/{id}', function( ){ });

$config->post('/demo/{id}', Controller::class)
    ->middlewareAppend(MyMiddleware::class);

$config->map('PUT', '/test', [Controller::class, 'controllerMethod'])
    ->middlewarePrepend([MyMiddleware::class, 'middlewareMethod']);

$config->map(['GET', 'POST'], '/test/{uuid}', function( ){  })
        ->middleware(function( ){ }); // alias for middlewareAppend

$config->group('/group', function(\Leven\Router\RouterConfigurator $group){
    $group->addMiddleware(MyMiddleware::class);

    $group->get('/inner', MyController::class); // path: /group/inner
    
    $group->group('/deep', function(\Leven\Router\RouterConfigurator $group2){
        $group2->put('/foo', function( ){ }); // path: /group/deep/foo
    });
});



// another way to define routes is by giving Route attributes to controller methods

class MyController {
    #[\Leven\Router\Route('GET', '/foo/{id}')]
    public function __invoke( ) { /* ... */ }

    #[\Leven\Router\Route(['GET', 'POST'], '/bar/baz', middleware: [Middleware::class])]
    public function controllerMethod( ) { /* ... */ }
}

// ControllerScanner will scan classes for methods with Route attributes and configure the Router accordingly
$scanner = new \Leven\Router\ControllerScanner($router);
$scanner->scanControllerClasses(MyController::class, );



// RouteUrlResolver helps generate URLs by reverse matching a controller to a path
$url = new \Leven\Router\RouteUrlResolver($router);
$url = new \Leven\Router\RouteUrlResolver($router, prefix: '/api/v1/');
$url = new \Leven\Router\RouteUrlResolver($router, prefix: 'https://example.com/');

// share the RouteUrlResolver, so it can be used in controllers
$injector->share($url);

// in the controller, use RouteUrlResolver like this:
$href = $url([MyController::class, 'controllerMethod']);
// and your route has parameters, pass them as the second argument:
$href = $url(MyController::class, ['id' => '123']);



// when writing a controller, keep in mind all parameters
// will be injected automatically by Auryn
class AnotherController {
    // if your controller class has many controller methods,
    // you can provide shared dependencies in the class constructor
    public function __construct(
        protected \Leven\Router\RouteUrlResolver $url,
        protected \Leven\Router\Messages\Request $request,
    ){}
    
    public function __invoke( ) {
        // access the dependencies from constructor:
        $href = ($this->url)(MyController::class);
    }
}



// route parameters can be accessed in the controller through
// the automatically injected RouteParams object
function fooController(\Leven\Router\RouteParams $params) {
    return 'Hello ' . $params->name;
}



// middlewares control the flow of the request:
// they can call the next middleware in the stack, or return a response

function myMiddleware( \Leven\Router\MiddlewareCallback $next ) {
    // if you want to prevent the request from continuing, don't call $next()
    // you can also modify the response returned by $next() before returning it
    
    return $next(); // this middleware doesn't do anything
}



// each controller should return a Response object
// if a string is returned, it will be converted
// into a Response object with the string as the body
function myController() {
    return new \Leven\Router\Messages\Response('Hello World!');
    return 'Hello World!'; // same as above
    
    return new \Leven\Router\Messages\Response('wrong request', 400);
    return new \Leven\Router\Messages\JsonResponse(['message' => 'foo']);
    return new \Leven\Router\Messages\HtmlResponse('<h1>no access!</h1>', '403');
    return new \Leven\Router\Messages\RedirectResponse('/foo');
}



// the Request object has the following properties:
$request->method // HTTP request method as a string
$request->path // path of the request as a string
$request->query // query string as an array
$request->body // request body if any (decoded)
$request->rawBody // raw request body if any
$request->headers // request headers as an array
$request->cookies // request cookies as an array
$request->files // request files as an array

// requests also contain custom attributes that may be useful
// for passing data between middleware and controllers
$request->setAttribute($name, $value);
$foo = $request->getAttribute($name);



// after configuring the Router, we match the Request to resolve a Route
// if no route is found, the Router will throw a RouteNotFoundException
try {
    $matchedRoute = $router->match($request);
} catch (\Leven\Router\Exception\RouteNotFoundException $e) {
    die('route not found');
}



// RouteHandler handles execution of a Route and its middleware stack
$handler = new \Leven\Router\RouteHandler($injector);

// it returns a Response object that we can manipulate if needed
$response = $handler->handle($matchedRoute);

// finally, we can send the response to the client
$response->dispatch();
```
