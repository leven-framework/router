<?php namespace Leven\Router\Response;

class EmptyResponse extends Response
{

    public string $body = '';
    public ?string $type = null;

    public function __construct(
        public int    $status = 204
    )
    {
    }

}
