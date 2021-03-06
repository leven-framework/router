<?php

namespace Leven\Router;

class RouteUrlResolver
{

    public function __construct(
        protected Router $router,
        protected string $prefix = '/',
        protected array $globalQuery = [],
    )
    {
    }

    /**
     * @throws Exception\RouteNotFoundException
     * @throws Exception\RouterConfigurationException
     */
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