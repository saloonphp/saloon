<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Body;

use Sammyjo20\Saloon\Repositories\Body\MultipartBodyRepository;

trait HasMultipartBody
{
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
        return $this->data ??= new MultipartBodyRepository($this->defaultBody());
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
