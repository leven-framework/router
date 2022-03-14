<?php namespace Leven\Router\Response;

class JsonResponse extends Response
{

    public ?string $type = 'application/json';

    public function __construct(
        array|string|bool|null $body,
        public int             $status = 200
    )
    {
        $this->body = json_encode($body);
    }

}
