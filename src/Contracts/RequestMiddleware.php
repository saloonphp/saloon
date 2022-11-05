<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Contracts;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Faking\SimulatedResponsePayload;

interface RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return PendingSaloonRequest|SimulatedResponsePayload|void
     */
    public function __invoke(PendingSaloonRequest $pendingRequest);
}
