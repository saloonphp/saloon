<?php

namespace Sammyjo20\Saloon\Traits\Body;

use Sammyjo20\Saloon\Helpers\ArrayBodyRepository;
use Sammyjo20\Saloon\Helpers\BodyRepository;

trait HasJsonBody
{
    /**
     * Body Repository
     *
     * @var BodyRepository
     */
    protected BodyRepository $body;

    /**
     * Retrieve the data repository
     *
     * @return ArrayBodyRepository
     */
    public function body(): ArrayBodyRepository
    {
        return $this->data ??= new ArrayBodyRepository($this->defaultData());
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
