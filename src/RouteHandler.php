<?php namespace Leven\Router;

use Auryn\{ConfigException, InjectionException, Injector};
use Leven\Router\Exception\RouterException;
use Leven\Router\Messages\Response;

class RouteHandler
{

    public function __construct(
        public Injector $injector,
    )
    {
    }

    /**
     * @throws InjectionException
     * @throws ConfigException
     */
    public function handle(Route $route): Response
    {
        $this->injector->share($route->getParams());

        $stack = [ new MiddlewareCallback($this->finalCallback(...)) ];

        foreach(array_reverse([...$route->middleware, $route->controller]) as $delegate)
            array_unshift($stack, new MiddlewareCallback(
                function() use ($stack, $delegate) {
                    $this->injector->share($stack[0]);
                    return $this->injector->execute($delegate);
                }
            ));

        return $stack[0]();
    }

    private function finalCallback(){
        $msg = 'no more callbacks in stack! you may only invoke MiddlewareCallback within middleware';
        throw new RouterException($msg);
    }

}