<?php namespace Leven\Router;

final class RouterConfigurator
{

    private string $pathPrefix = '';
    private array $middleware = [];

    public function __construct(
        private Router $router
    )
    {
    }

    public function setPathPrefix(string $prefix): void
    {
        $this->pathPrefix = trim($prefix, '/');
    }

    public function addMiddleware(array|string|callable ...$middleware): void
    {
        $this->middleware += [...$middleware];
    }



    public function group(string $path, callable $callback): void
    {
        if($this->pathPrefix)
            $path = $this->pathPrefix . '/' . ltrim($path, '/');

        $group = new RouterConfigurator($this->router);
        $group->setPathPrefix($path);
        $group->addMiddleware(...$this->middleware);

        $callback($group);
        unset($group);
    }


    public function map(string|array $methods, string $path, string|array|callable $controller): Route
    {
        if($this->pathPrefix)
            $path = $this->pathPrefix . '/' . ltrim($path, '/');

        $route = new Route(
            methods: $methods,
            path: $path,
            controller: $controller,
            middleware: $this->middleware
        );

        $this->router->register($route);
        return $route;
    }

    public function get(string $path, string|array|callable $controller): Route
    {
        return $this->map('GET', $path, $controller);
    }

    public function post(string $path, string|array|callable $controller): Route
    {
        return $this->map('POST', $path, $controller);
    }

    public function put(string $path, string|array|callable $controller): Route
    {
        return $this->map('PUT', $path, $controller);
    }

    public function delete(string $path, string|array|callable $controller): Route
    {
        return $this->map('DELETE', $path, $controller);
    }

}
