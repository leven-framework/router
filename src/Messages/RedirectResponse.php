<?php

namespace Leven\Router\Messages;

class RedirectResponse extends Response
{
    public string $body = '';
    public array $headers = [];

    public function __construct(
        string $url,
        public int $status = 302,
    )
    {
        $this->addHeader('Location', $url);
    }
}