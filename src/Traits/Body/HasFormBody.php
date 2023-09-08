<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Http\PendingRequest;
use Saloon\Repositories\Body\FormBodyRepository;

trait HasFormBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     */
    protected FormBodyRepository $body;

    /**
     * Boot the HasFormBody trait
     */
    public function bootHasFormBody(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Content-Type', 'application/x-www-form-urlencoded');
    }

    /**
     * Retrieve the data repository
     */
    public function body(): FormBodyRepository
    {
        return $this->body ??= new FormBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [];
    }
}
