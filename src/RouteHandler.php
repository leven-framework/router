<?php namespace Leven\Router;

use Auryn\{ConfigException, InjectionException, Injector};
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
        $this->injector->share($route);

        $callbacks = [];
        foreach(array_reverse([...$route->middleware, $route->controller]) as $delegate){
            array_unshift($callbacks, function() use ($callbacks, $delegate) {
                $args = !empty($callbacks) ? [':next' => $callbacks[0]] : [];
                return Response::wrap($this->injector->execute($delegate, $args));
            });
        }
        return $callbacks[0]();
    }

}