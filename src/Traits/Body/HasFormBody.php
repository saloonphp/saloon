<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Repositories\Body\FormBodyRepository;

trait HasFormBody
{
    use ChecksForWithBody;

    /**
     * Body Repository
     *
     * @var FormBodyRepository
     */
    protected FormBodyRepository $body;

    /**
     * Retrieve the data repository
     *
     * @return FormBodyRepository
     */
    public function body(): FormBodyRepository
    {
        return $this->body ??= new FormBodyRepository($this->defaultBody());
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
