<?php

declare(strict_types=1);

namespace Saloon\Exceptions\Request;

use Throwable;
use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;
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
        return self::getMessageForStatusCode($this->getStatus());
    }

    /**
     * Status code mapper
     *
     * @param int $code
     * @return string|null
     */
    protected static function getMessageForStatusCode(int $code): ?string
    {
        return match ($code) {
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-status',
            208 => 'Already Reported',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
            default => null,
        };
    }
}
