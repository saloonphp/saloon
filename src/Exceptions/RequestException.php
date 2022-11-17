<?php declare(strict_types=1);

namespace Saloon\Exceptions;

use Throwable;
use Saloon\Http\Responses\Response;

class RequestException extends SaloonException
{
    /**
     * The Saloon AbstractResponse
     *
     * @var Response
     */
    protected Response $saloonResponse;

    /**
     * Create the RequestException
     *
     * @param Response $response
     * @param $message
     * @param $code
     * @param Throwable|null $previous
     */
    public function __construct(Response $response, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->saloonResponse = $response;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the Saloon AbstractResponse Class.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->saloonResponse;
    }
}
