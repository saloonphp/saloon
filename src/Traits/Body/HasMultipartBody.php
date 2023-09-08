<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Http\PendingRequest;
use Saloon\Repositories\Body\MultipartBodyRepository;

trait HasMultipartBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     */
    protected MultipartBodyRepository $body;

    /**
     * Boot the HasMultipartBody trait
     *
     * @throws \Exception
     */
    public function bootHasMultipartBody(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Content-Type', 'multipart/form-data; boundary=' . $this->body()->getBoundary());
    }

    /**
     * Retrieve the data repository
     *
     * @throws \Exception
     */
    public function body(): MultipartBodyRepository
    {
        return $this->body ??= new MultipartBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return array<\Saloon\Data\MultipartValue>
     */
    protected function defaultBody(): array
    {
        return [];
    }
}
