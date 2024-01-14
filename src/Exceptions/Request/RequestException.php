<?php

declare(strict_types=1);

namespace Saloon\Exceptions\Request;

use Throwable;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Helpers\StatusCodeHelper;
use Saloon\Exceptions\SaloonException;

/**
 * RequestException
 *
 * This exception is thrown when the response from a request is a failed response.
 *
 * @see https://docs.saloon.dev/the-basics/handling-failures
 */
class RequestException extends SaloonException
{
    /**
     * The Saloon Response
     */
    protected Response $response;

    /**
     * Maximum length allowed for the body
     */
    protected int $maxBodyLength = 200;

    /**
     * Create the RequestException
     */
    public function __construct(Response $response, ?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $this->response = $response;

        if (is_null($message)) {
            $status = $this->getStatus();
            $statusCodeMessage = $this->getStatusMessage() ?? 'Unknown Status';
            $rawBody = $response->body();
            $exceptionBodyMessage = mb_strlen($rawBody) > $this->maxBodyLength ? mb_substr($rawBody, 0, $this->maxBodyLength) : $rawBody;

            $message = sprintf('%s (%s) Response: %s', $statusCodeMessage, $status, $exceptionBodyMessage);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the Saloon Response Class.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Get the pending request.
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->getResponse()->getPendingRequest();
    }

    /**
     * Get the HTTP status code
     */
    public function getStatus(): int
    {
        return $this->response->status();
    }

    /**
     * Get the status message
     */
    public function getStatusMessage(): ?string
    {
        return StatusCodeHelper::getMessage($this->getStatus());
    }
}
