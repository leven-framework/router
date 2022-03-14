<?php namespace Leven\Router\Middleware;

use Leven\Router\Request;
use Leven\Router\Response\Response;

class OutputBufferingMiddleware
{

    public function before(): void
    {
        ob_start();
    }

    public function after(): Response
    {
        $output = ob_get_clean();
        return new Response($output);
    }

}