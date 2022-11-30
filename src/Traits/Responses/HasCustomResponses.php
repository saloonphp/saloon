<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

trait HasCustomResponses
{
    /**
     * Specify a default response.
     *
     * When an empty string, the response on the sender will be used.
     *
     * @var string
     */
    protected string $response = '';

    /**
     * Resolve the custom response class
     *
     * @return string
     */
    public function resolveResponseClass(): string
    {
        return $this->response;
    }
}
