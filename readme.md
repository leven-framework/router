## Example usage

```php
<?php

require 'vendor/autoload.php';

$injector = new Auryn\Injector();
// here you can share objects using $injector->share() ; check https://github.com/rdlowrey/auryn for more info

$request = Leven\Router\Messages\Request::fromSuperglobals();
$injector->share($request);

// this is the router object that holds the route registry and performs matching
$router = new Leven\Router\Router();

$router->addGlobalMiddleware(Middleware::class);
// all routes added after this will have this middleware prepended to their middleware stack



$config = new Leven\Router\RouterConfigurator($router);
// RouterConfigurator provides simple methods for configuring routes

$config->get('/test1/{id}', Controller::class)->middlewareAppend([Middleware::class]); // will use the __invoke method as middleware
// OR
$config->map('GET','/test2', [Controller::class, 'controllerMethod'])->middlewarePrepend([Middleware::class]);
// OR
$config->map(['GET', 'POST'], '/test3/{uuid}', function(Leven\Router\RouteParams $params){ /* ... */ })
    ->middleware([Middleware::class]); // alias to middlewareAppend
// OR
$config->group('/group', function(Leven\Router\RouterConfigurator $group){
    // prepends middleware to all routes within this group added from now on
    $group->addMiddleware(Middleware::class);

    $group->post('/inner', Controller::class); // path will be /group/inner
    // you can nest groups within groups as well
});



// you can also scan route method attributes from a class and add them into a router object
$scanner = new Leven\Router\ControllerScanner($router);
$scanner->scanControllerClasses(Controller::class);


class Middleware {
    public function __invoke(Leven\Router\MiddlewareCallback $next){
        echo 'i am middleware!';
        $response = $next();
        echo 'its middleware again!';
        return $response;
    }
}

class Controller {
    public function __construct(
        public Leven\Router\Messages\Request $request,
        // dependencies such as Request or RouteParams can be
        // either in constructor or params for controller class
    ){}

    #[Leven\Router\Route('GET', '/test1/{id}', middleware: [Middleware::class])]
    public function __invoke(Leven\Router\RouteParams $params){
        echo "i am controller! the url param id is $params->id";
        // it's preferred to return a response object or string than echo directly

        return new Leven\Router\Messages\HtmlResponse('this is content of a response object!');
    }

    public function controllerMethod(Leven\Router\RouteUrlResolver $url){
        $query = $this->request->query['test'];

        // do a reverse match and find path of a controller, we also add the id param value
        $link = $url(Controller::class, ['id' => $query]);
        return "some text and a link $link"; // will be wrapped into a response object
    }
}



// RouteUrlResolver helps generate url by reverse matching a controller to a path
$injector->share( new Leven\Router\RouteUrlResolver($router, 'http://domain.tld/') );

try {
    $route = $router->match($request);
} catch (Leven\Router\Exception\RouteNotFoundException $e) {
    die('route not found');
}

// this is the only currently available route handler that requires an Auryn injector to work
$handler = new Leven\Router\RouteHandler($injector);
$response = $handler->handle($route); // response object returned in controller or middleware

// send the response over http
$response->dispatch();
```