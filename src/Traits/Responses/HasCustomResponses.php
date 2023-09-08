<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

trait HasCustomResponses
{
    /**
     * Specify a default response.
     *
     * When null or an empty string, the response on the sender will be used.
     *
     * @var class-string<\Saloon\Http\Response>|null
     */
    protected ?string $response = null;

    /**
     * Resolve the custom response class
     *
     * @return class-string<\Saloon\Http\Response>|null
     */
    public function resolveResponseClass(): ?string
    {
        return $this->response ?? null;
    }
}
