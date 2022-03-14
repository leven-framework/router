<?php namespace Leven\Router;

use Leven\Router\Exception\InvalidRequestException;

class Request
{

    public ?string $rawBody = null;
    public ?array $files = null;

    public function __construct(
        public string $method,
        public string $path,
        public array $query = [],
        public ?array $body = null,
        public array $headers = []
    )
    {
    }

    /**
     * @throws InvalidRequestException
     */
    public static function fromSuperglobals(): static
    {
        if(!isset($_SERVER['REQUEST_URI'])) throw new InvalidRequestException('cannot find HTTP headers in $_SERVER');

        $uri = parse_url($_SERVER['REQUEST_URI']);

        $request = new static(
            method: $_SERVER['REQUEST_METHOD'],
            path: $uri['path'] ?? '',
            headers: $_SERVER
        );

        parse_str($uri['query'] ?? '', $request->query);

        $request->rawBody = file_get_contents('php://input');

        if(!empty($_POST)){
            $request->body = $_POST;
            $request->files = $_FILES;
        }
        else $request->decodeBody();

        return $request;
    }

    /**
     * @throws InvalidRequestException
     */
    public function decodeBody(): void
    {
        if(!$this->rawBody) return;

        switch( explode(';', $this->headers['CONTENT_TYPE'])[0] ){

            case 'application/json':
                $this->body = json_decode($this->rawBody, true);
                if($this->body === null && json_last_error() !== JSON_ERROR_NONE)
                    throw new InvalidRequestException('invalid json in request body');
                break;

        }
    }

}
