<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Repositories\Body\StringBodyRepository;

trait HasBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     *
     * @var \Saloon\Repositories\Body\StringBodyRepository
     */
    protected StringBodyRepository $body;

    /**
     * Retrieve the data repository
     *
     * @return \Saloon\Repositories\Body\StringBodyRepository
     */
    public function body(): StringBodyRepository
    {
        return $this->body ??= new StringBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return string|null
     */
    protected function defaultBody(): ?string
    {
        return null;
    }
}
