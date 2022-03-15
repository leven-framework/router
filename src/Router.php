<?php namespace Leven\Router;

use Leven\Router\Exception\{RouteNotFoundException, RouterException};
use Leven\Router\Messages\Request;

class Router
{

    private array $store = [];
    private array $reverseStore = [];


    /**
     * @throws RouterException
     */
    public function register(Route $route): void
    {
        if(isset($this->store[$route->method][$route->path]))
            throw new RouterException('route path already defined');

        $this->store[$route->method][$route->path] = $route;

        if(!is_callable($route->controller))
            $this->reverseStore[implode('::', $route->controller)] = $route;
    }

    /**
     * @throws RouteNotFoundException
     */
    public function match(Request $request): Route
    {
        $method = strtoupper($request->method);
        $path = trim(strtolower($request->path), '/');

        $pathParts = explode('/', $path);
        $partsNum = count($pathParts);
        for($i = 0 ; $i < 2 ** $partsNum ; $i++){
            $bin = decbin($i);
            $try = $pathParts;

            for($j = strlen($bin) - 1 ; $j >= 0 ; $j--)
                if($bin[strlen($bin) - 1 - $j]) {
                    $try[$partsNum - 1 - $j] = '$WILDCARD$';
                    $params[] = $pathParts[$partsNum - 1 - $j];
                }

            $try = implode('/', $try);
            if(isset($this->store[$method][$try])) {
                /** @var Route $route */
                $route = $this->store[$method][$try];
                $route->params = array_reverse($params ?? []);
                return $route;
            }
        }

        throw new RouteNotFoundException;
    }

    public function reverse(string|array $controller)
    {
        if(is_array($controller)) $controller = implode('::', $controller);

        if(empty($this->reverseStore[$controller]))
            throw new RouterException("controller $controller not registered in router");

        return $this->reverseStore[$controller];
    }

}