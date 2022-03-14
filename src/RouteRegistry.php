<?php namespace Leven\Router;

use Leven\Router\Exception\{RouteNotFoundException, RouterException};
use ReflectionClass, ReflectionException, ReflectionMethod;

class RouteRegistry
{

    private array $store = [];


    public function getStore(): array
    {
        return $this->store;
    }

    /**
     * @throws RouterException
     */
    public function addRoute(Route $route): void
    {
        if(isset($this->store[$route->method][$route->path]))
            throw new RouterException('route path already defined');

        $this->store[$route->method][$route->path] = $route;
    }

    /**
     * @throws ReflectionException
     * @throws RouterException
     */
    public function scanControllerClass(string $class): void
    {
        foreach((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
            foreach($method->getAttributes(Route::class) as $attribute) {
                $route = $attribute->newInstance();
                $route->controller = [$class, $method->name];
                $this->addRoute($route);
            }
    }

    /**
     * @throws ReflectionException
     * @throws RouterException
     */
    public function scanControllerClasses(string ...$classes): void
    {
        foreach($classes as $class) $this->scanControllerClass($class);
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
            $args = [];

            for($j = strlen($bin) - 1 ; $j >= 0 ; $j--)
                if($bin[strlen($bin) - 1 - $j]) {
                    $try[$partsNum - 1 - $j] = '$WILDCARD$';
                    $args[] = $pathParts[$partsNum - 1 - $j];
                }

            $try = implode('/', $try);
            if(isset($this->store[$method][$try])) {
                $route = $this->store[$method][$try];
                $route->controllerArgs = array_reverse($args);
                return $route;
            }
        }

        throw new RouteNotFoundException;
    }

}