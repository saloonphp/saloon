<?php

namespace Sammyjo20\Saloon\Traits\Body;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
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
     * Boot the plugin
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public function bootHasJsonBody(PendingSaloonRequest $request): void
    {
        // Todo: Make sure that request headers have the highest priority.

        $request->headers()->add('Content-Type', 'application/json');
    }

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
