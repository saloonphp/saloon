<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Body;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Repositories\Body\JsonBodyRepository;

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
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function bootHasJsonBody(PendingSaloonRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Content-Type', 'application/json');
    }

    /**
     * Retrieve the data repository
     *
     * @return JsonBodyRepository
     */
    public function body(): JsonBodyRepository
    {
        return $this->body ??= new JsonBodyRepository($this->defaultBody());
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
