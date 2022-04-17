<?php

namespace Leven\Router;

use ReflectionClass, ReflectionMethod, ReflectionException;

class ControllerScanner
{

    public function __construct(
        protected Router $router
    )
    {
    }

    /**
     * @throws ReflectionException
     */
    public function scanControllerClass(string $class): void
    {
        foreach((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
            foreach($method->getAttributes(Route::class) as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();
                $route->controller = [$class, $method->name];
                $this->router->register($route);
            }
    }

    /**
     * @throws ReflectionException
     */
    public function scanControllerClasses(string ...$classes): void
    {
        foreach($classes as $class) $this->scanControllerClass($class);
    }

}