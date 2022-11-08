<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Body;

use Sammyjo20\Saloon\Repositories\Body\StringBodyRepository;

trait HasBody
{
    /**
     * Body Repository
     *
     * @var StringBodyRepository
     */
    protected StringBodyRepository $body;

    /**
     * Retrieve the data repository
     *
     * @return StringBodyRepository
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
