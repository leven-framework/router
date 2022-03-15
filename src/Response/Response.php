<?php namespace Leven\Router\Response;

class Response
{

    public function __construct(
        public string $body = '',
        public int    $status = 200,
        public ?string $type = 'text/plain'
    )
    {
    }

    public static function responsify($response): static{
        if($response instanceof static) return $response;
        return new Response((string) $response);
    }

    public function __invoke(): never
    {
        http_response_code($this->status);

        if(!empty($this->type)) {
            header('Content-Type: ' . $this->type);
        }

        print($this->body);
        exit;
    }

}
