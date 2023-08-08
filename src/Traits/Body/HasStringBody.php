<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Repositories\Body\StringBodyRepository;

trait HasStringBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     */
    protected StringBodyRepository $body;

    /**
     * Retrieve the data repository
     */
    public function body(): StringBodyRepository
    {
        return $this->body ??= new StringBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     */
    protected function defaultBody(): ?string
    {
        return null;
    }
}
