<?php

namespace Leven\Router;

class RouteUrlResolver
{

    public function __construct(
        public Router $router,
        public string $prefix = '/',
        public array $globalQuery = [],
    )
    {
    }

    public function __invoke(
        string|array $controller,
        array $params = [],
        array $query = [],
        bool $overrideGlobalQuery = false,
    ): string
    {
        if(!$overrideGlobalQuery) $query = $this->globalQuery + $query;

        return
            $this->prefix .
            $this->router->reverse($controller)->generatePath($params) .
            (!empty($query) ? '?' . http_build_query($query) : '')
        ;
    }

}