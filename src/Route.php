<?php namespace Leven\Router;

use Attribute;
use Closure;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{

    public string $method;
    public string $path;

    public array $controllerParams = [];
    public ?array $controllerArgs = null;

    public function __construct(
        string $method,
        string $path,
        public null|array|Closure $controller = null,
        public array $middleware = []
    )
    {
        $this->method = strtoupper($method);

        $pathParts = explode('/', trim($path, '/'));
        foreach($pathParts as $index => $part){
            if (!str_starts_with($part, '{') || !str_ends_with($part, '}')){
                $pathParts[$index] = strtolower($part);
                continue;
            }

            $pathParts[$index] = '$WILDCARD$';
            $this->controllerParams[] = trim($part, '{}');
        }
        $this->path = implode('/', $pathParts);
    }

    public function middleware(array $middleware): static
    {
        $this->middleware += [...$middleware];
        return $this;
    }

}