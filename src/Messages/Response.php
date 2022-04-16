<?php namespace Leven\Router\Messages;

class Response
{

    public array $cookies = [];

    public function __construct(
        public string $body = '',
        public int    $status = 200,
        public array  $headers = [
            'Content-Type' => 'text/plain'
        ],
    )
    {
    }

    public static function wrap(mixed $response): static
    {
        if($response instanceof Response) return $response;
        return new static((string) $response);
    }

    public function dispatch(): never
    {
        http_response_code($this->status);

        foreach($this->cookies as $cookie)
            setcookie(...$cookie);

        foreach($this->headers as $header => $value)
            header("$header: $value");

        print($this->body);
        exit;
    }

    public function setHeader(string $headerName, string $headerValue): static
    {
        $this->headers[$headerName] = $headerValue;
        return $this;
    }

    public function setFilenameHeader(string $filename): static
    {
        $this->setHeader('Content-Disposition', "inline; filename=\"$filename\"");
        return $this;
    }

    public function setContentTypeHeader(string $contentType): static
    {
        $this->setHeader('Content-Type', $contentType);
        return $this;
    }

    public function setCookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false
    ): static
    {
        $this->cookies[$name] = [
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
        ];
        return $this;
    }

}
