<?php namespace Leven\Router\Messages;

class HtmlResponse extends Response
{

    public array $headers = [
        'Content-Type' => 'text/html'
    ];

    public function __construct(
        public string $body,
        public int    $status = 200
    )
    {
    }

}
