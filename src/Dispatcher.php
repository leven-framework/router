<?php namespace Leven\Router;

use Auryn\{ConfigException, InjectionException, Injector};
use Leven\Router\{Exception\ResponseException, Response\Response};

class Dispatcher
{

    /**
     * @throws InjectionException
     * @throws ConfigException
     */
    public function __invoke(Route $route, Injector $injector): Response
    {
        $injector->share($route);

        $controllerArgs = array_combine(
            array_map( fn($k) => ":$k", $route->controllerParams ),
            $route->controllerArgs
        );

        try {
            foreach ($route->middleware as $index => $value) {
                $class = is_numeric($index) ? $value : $index;
                if (method_exists($class, 'before')) {
                    $response = $injector->execute( [$class, 'before'] );
                    if (!is_numeric($index)) $controllerArgs[":$value"] = $response;
                }
            }

            $response = Response::responsify(
                $injector->execute($route->controller, $controllerArgs)
            );

            foreach (array_reverse($route->middleware) as $index => $value) {
                $class = is_numeric($index) ? $value : $index;
                if (method_exists($class, 'after')) {
                    $response = Response::responsify(
                        $injector->execute( [$class, 'after'], [":response" => $response] )
                    );
                }
            }
        } catch (ResponseException $e) {
            return $e->getResponse();
        }

        return $response;
    }

}