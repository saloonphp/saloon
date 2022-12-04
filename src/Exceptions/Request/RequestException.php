<?php

declare(strict_types=1);

namespace Saloon\Exceptions\Request;

use Saloon\Contracts\Response;
use Saloon\Exceptions\SaloonException;
use Throwable;

class RequestException extends SaloonException
{
    /**
     * The Saloon Response
     *
     * @var Response
     */
    protected Response $response;

    /**
     * Create the RequestException
     *
     * @param \Saloon\Contracts\Response $response
     * @param $message
     * @param $code
     * @param \Throwable|null $previous
     */
    public function __construct(Response $response, $message = '', $code = 0, ?Throwable $previous = null)
    {
        $this->response = $response;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the Saloon Response Class.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
