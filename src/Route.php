<?php namespace Leven\Router;

use Attribute;
use Closure;
use Leven\Router\Exception\RouterException;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{

    public array $methods;
    public string $path;

    public array $paramNames = [];
    public ?array $paramValues = null;

    public function __construct(
        string|array              $methods,
        string                    $path,
        public null|array|Closure $controller = null,
        public array              $middleware = []
    )
    {
        if(is_string($methods)) $methods = [$methods];
        foreach($methods as &$method) $method = strtoupper($method);
        $this->methods = $methods;

        $pathParts = explode('/', trim($path, '/'));
        foreach($pathParts as $index => $part){
            if (!str_starts_with($part, '{') || !str_ends_with($part, '}')){
                $pathParts[$index] = strtolower($part);
                continue;
            }

            $pathParts[$index] = '$WILDCARD$';
            $this->paramNames[] = trim($part, '{}');
        }
        $this->path = implode('/', $pathParts);
    }

    public function middleware(array $middleware): static
    {
        $this->middleware += [...$middleware];
        return $this;
    }

    public function generatePath(array $params = []): string
    {
        $pathParts = explode('/', $this->path);
        $paramIndex = 0;

        foreach($pathParts as &$part)
            if($part === '$WILDCARD$'){
                $paramName = $this->paramNames[$paramIndex++];
                if(empty($params[$paramName]))
                    throw new RouterException("expected param $paramName for this route");
                $part = $params[$paramName];
            }

        return '/' . implode('/', $pathParts);
    }

    public function getParams(): RouteParams
    {
        return new RouteParams($this->paramNames, $this->paramValues);
    }

}