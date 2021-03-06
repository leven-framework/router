<?php namespace Leven\Router;

use Leven\Router\Exception\RouteNotFoundException;
use Leven\Router\Messages\Request;

class Router
{

    protected array $store = [];
    protected array $reverseStore = [];

    protected array $globalMiddleware = [];


    public function addGlobalMiddleware(array|string|callable ...$middleware): void
    {
        $this->globalMiddleware += [...$middleware];
    }


    public function register(Route $route): void
    {
        $route->middlewarePrepend(...$this->globalMiddleware);

        foreach($route->methods as $method)
            $this->store[$method][$route->path] = $route;

        if (is_array($route->controller)) {
            $controllerString = implode('::', $route->controller);
            $this->reverseStore[$controllerString] = $route;
        } else
        if (is_string($route->controller)) {
            $this->reverseStore[$route->controller] = $route;
            $this->reverseStore["$route->controller::__invoke"] = $route;
        }
    }

    /**
     * @throws RouteNotFoundException
     */
    public function match(Request $request): Route
    {
        $method = strtoupper($request->method);
        $path = trim($request->path, '/');
        $pathParts = explode('/', strtolower($path));
        $pathPartsOriginal = explode('/', $path); // for pulling correctly capitalized params
        $partsNum = count($pathParts);

        for($i = 0 ; $i < 2 ** $partsNum ; $i++){
            $bin = decbin($i);
            $try = $pathParts;
            $params = [];

            for($j = strlen($bin) - 1 ; $j >= 0 ; $j--)
                if($bin[strlen($bin) - 1 - $j]) {
                    $try[$partsNum - 1 - $j] = '$WILDCARD$';
                    $params[] = $pathPartsOriginal[$partsNum - 1 - $j];
                }

            $try = implode('/', $try);
            if(isset($this->store[$method][$try])) {
                /** @var Route $route */
                $route = $this->store[$method][$try];
                $route->paramValues = $params;
                return $route;
            }
        }

        throw new RouteNotFoundException;
    }

    /**
     * @throws RouteNotFoundException
     */
    public function reverse(string|array $controller): Route
    {
        if(is_array($controller)) $controller = implode('::', $controller);

        if(empty($this->reverseStore[$controller]))
            throw new RouteNotFoundException;

        return $this->reverseStore[$controller];
    }

}