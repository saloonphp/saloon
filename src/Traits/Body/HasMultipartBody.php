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
     * @var MultipartBodyRepository
     */
    protected MultipartBodyRepository $body;

    /**
     * Retrieve the data repository
     *
     * @return MultipartBodyRepository
     */
    public function body(): MultipartBodyRepository
    {
        return $this->body ??= new MultipartBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return array
     */
    protected function defaultBody(): array
    {
        return [];
    }
}
