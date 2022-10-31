<?php

namespace Sammyjo20\Saloon\Traits\Body;

use Sammyjo20\Saloon\Repositories\JsonBodyRepository;

trait HasJsonBody
{
    /**
     * Body Repository
     *
     * @var JsonBodyRepository
     */
    protected JsonBodyRepository $body;

    /**
     * Retrieve the data repository
     *
     * @return JsonBodyRepository
     */
    public function body(): JsonBodyRepository
    {
        return $this->data ??= new JsonBodyRepository($this->defaultBody());
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
