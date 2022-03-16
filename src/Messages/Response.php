<?php namespace Leven\Router\Messages;

class Response
{

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
        return new Response((string) $response);
    }

    public function dispatch(): never
    {
        http_response_code($this->status);

        foreach($this->headers as $header => $value)
            header("$header: $value");

        print($this->body);
        exit;
    }

    public function addHeader(string $headerName, string $headerValue): static
    {
        $this->headers[$headerName] = $headerValue;
        return $this;
    }

    public function addFilenameHeader(string $filename): static
    {
        $this->addHeader('Content-Disposition', "inline; filename=\"$filename\"");
        return $this;
    }

}
