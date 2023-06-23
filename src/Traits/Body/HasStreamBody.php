<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Psr\Http\Message\StreamInterface;
use Saloon\Repositories\Body\StreamBodyRepository;

trait HasStreamBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     *
     * @var \Saloon\Repositories\Body\StreamBodyRepository
     */
    protected StreamBodyRepository $body;

    /**
     * Retrieve the data repository
     *
     * @return \Saloon\Repositories\Body\StreamBodyRepository
     */
    public function body(): StreamBodyRepository
    {
        return $this->body ??= new StreamBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return StreamInterface|resource|null
     */
    protected function defaultBody(): mixed
    {
        return null;
    }
}
