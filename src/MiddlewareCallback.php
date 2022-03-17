<?php

namespace Leven\Router;

use Closure;
use Leven\Router\Messages\Response;

class MiddlewareCallback
{

    public function __construct(
        private Closure $callback
    )
    {
    }

    public function __invoke(): Response
    {
        return Response::wrap( ($this->callback)() );
    }

}