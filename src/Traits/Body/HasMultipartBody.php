<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

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
     * Retrieve the data repository
     *
     * @return \Saloon\Repositories\Body\MultipartBodyRepository
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
