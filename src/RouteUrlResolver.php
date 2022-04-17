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
        array        $params = [],
        array        $query = [],
        bool         $replaceGlobalQuery = false,
    ): string
    {
        if(!$replaceGlobalQuery) $query = $this->globalQuery + $query;

        return
            $this->prefix .
            $this->router->reverse($controller)->generatePath($params) .
            (!empty($query) ? '?' . http_build_query($query) : '')
        ;
    }

}