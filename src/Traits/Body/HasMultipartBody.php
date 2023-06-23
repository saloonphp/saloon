<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Contracts\PendingRequest;
use Saloon\Repositories\Body\MultipartBodyRepository;

trait HasMultipartBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     *
     * @var \Saloon\Repositories\Body\MultipartBodyRepository
     */
    protected MultipartBodyRepository $body;

    /**
     * Boot the HasMultipartBody trait
     *
     * @param PendingRequest $pendingRequest
     * @return void
     * @throws \Exception
     */
    public function bootHasMultipartBody(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Content-Type', 'multipart/form-data; boundary=' . $this->body()->getBoundary());
    }

    /**
     * Retrieve the data repository
     *
     * @return \Saloon\Repositories\Body\MultipartBodyRepository
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
