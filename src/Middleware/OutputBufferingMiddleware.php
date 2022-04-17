<?php namespace Leven\Router\Middleware;

use Leven\Router\Messages\Response;
use Leven\Router\MiddlewareCallback;

class OutputBufferingMiddleware
{

    public function __invoke(MiddlewareCallback $next): Response
    {
        return $this->replaceBody($next);
    }

    public function replaceBody(MiddlewareCallback $next): Response
    {
        ob_start();
        $next(); // ignoring returned response
        $output = ob_get_clean();

        return new Response($output);
    }

    public function prependToBody(MiddlewareCallback $next): Response
    {
        ob_start();
        $response = $next();
        $output = ob_get_clean();

        $response->body = $output . $response->body;
        return $response;
    }

    public function appendToBody(MiddlewareCallback $next): Response
    {
        ob_start();
        $response = $next();
        $output = ob_get_clean();

        $response->body .= $output;
        return $response;
    }

}