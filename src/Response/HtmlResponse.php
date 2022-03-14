<?php namespace Leven\Router\Response;

class HtmlResponse extends Response
{

    public ?string $type = 'text/html';

    public function __construct(
        public array|string|bool|null $body,
        public int                    $status = 200
    )
    {
    }

}
