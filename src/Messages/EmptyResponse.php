<?php namespace Leven\Router\Messages;

class EmptyResponse extends Response
{

    public string $body = '';
    public array $headers = [];

    public function __construct(
        public int $status = 204
    )
    {

    }

}
