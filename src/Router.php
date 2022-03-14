<?php namespace Leven\Router;

final class Router
{

    private string $pathPrefix = '';
    private array $globalMiddleware = [];

    public function __construct(
        private RouteRegistry $registry = new RouteRegistry
    )
    {
    }

    public function getRegistry(): RouteRegistry
    {
        return $this->registry;
    }

    public function setPathPrefix(string $prefix): void
    {
        $this->pathPrefix = trim($prefix, '/');
    }

    public function addGlobalMiddleware(array $middleware): void
    {
        $this->globalMiddleware += [...$middleware];
    }



    public function group(string $path, callable $callback): void
    {
        if($this->pathPrefix)
            $path = $this->pathPrefix . '/' . ltrim($path, '/');

        $group = new Router($this->registry);
        $group->setPathPrefix($path);

        $callback($group);
        unset($group);
    }


    /**
     * @throws Exception\RouterException
     */
    public function add(string $method, string $path, array|callable $controller): Route
    {
        if($this->pathPrefix)
            $path = $this->pathPrefix . '/' . ltrim($path, '/');

        $route = new Route(
            method: $method,
            path: $path,
            controller: $controller,
            middleware: $this->globalMiddleware
        );

        $this->registry->addRoute($route);
        return $route;
    }

    public function get(string $path, array|callable $controller): Route
    {
        return $this->add('GET', $path, $controller);
    }

    public function post(string $path, array|callable $controller): Route
    {
        return $this->add('POST', $path, $controller);
    }

    public function put(string $path, array|callable $controller): Route
    {
        return $this->add('PUT', $path, $controller);
    }

    public function delete(string $path, array|callable $controller): Route
    {
        return $this->add('DELETE', $path, $controller);
    }

}
