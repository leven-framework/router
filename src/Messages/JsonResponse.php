<?php namespace Leven\Router\Messages;

class JsonResponse extends Response
{

    public array $headers = [
        'Content-Type' => 'application/json'
    ];

    public function __construct(
        mixed      $body,
        public int $status = 200
    )
    {
        $this->body = json_encode($body);
    }

}
