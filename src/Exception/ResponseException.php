<?php namespace Leven\Router\Exception;

use Exception;
use Leven\Router\Response\Response;

final class ResponseException extends Exception
{

    public function __construct(
        private readonly Response $response
    )
    {
        parent::__construct();
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

}
