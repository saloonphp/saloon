<?php

declare(strict_types=1);

namespace Saloon\Exceptions\Request;

use Throwable;
use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;
use Saloon\Helpers\StatusCodeHelper;
use Saloon\Exceptions\SaloonException;

class RequestException extends SaloonException
{
    /**
     * The Saloon Response
     *
     * @var \Saloon\Contracts\Response
     */
    protected Response $response;

    /**
     * Maximum length allowed for the body
     *
     * @var int
     */
    protected int $maxBodyLength = 200;

    /**
     * Create the RequestException
     *
     * @param \Saloon\Contracts\Response $response
     * @param string|null $message
     * @param int $code
     * @param \Throwable|null $previous
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
     *
     * @return \Saloon\Contracts\Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Get the pending request.
     *
     * @return \Saloon\Contracts\PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->getResponse()->getPendingRequest();
    }

    /**
     * Get the HTTP status code
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->response->status();
    }

    /**
     * Get the status message
     *
     * @return string|null
     */
    public function getStatusMessage(): ?string
    {
        return StatusCodeHelper::getMessage($this->getStatus());
    }
}
