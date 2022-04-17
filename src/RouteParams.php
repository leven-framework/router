<?php

namespace Leven\Router;

use Leven\Router\Exception\RouterConfigurationException;

class RouteParams
{

    public array $params = [];

    public function __construct(
        array $paramNames,
        ?array $paramValues,
    )
    {
        if($paramValues === null) return;

        foreach($paramNames as $index => $name)
            $this->params[$name] = $paramValues[$index];
    }

    public function __get(string $name): string
    {
        if(!isset($this->params[$name]))
            throw new RouterConfigurationException("param $name not defined");

        return $this->params[$name];
    }

}