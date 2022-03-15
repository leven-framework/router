<?php namespace Leven\Router\Middleware;

use Leven\Router\Messages\Response;

class OutputBufferingMiddleware
{

    public function __invoke($next): Response
    {
        return $this->replaceBody($next);
    }

    public function replaceBody($next): Response
    {
        ob_start();
        $next(); // ignoring returned response
        $output = ob_get_clean();

        return new Response($output);
    }

    public function prependToBody($next): Response
    {
        ob_start();
        $response = $next();
        $output = ob_get_clean();

        $response->body = $output . $response->body;
        return $response;
    }

    public function appendToBody($next): Response
    {
        ob_start();
        $response = $next();
        $output = ob_get_clean();

        $response->body .= $output;
        return $response;
    }

}