<?php declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\PendingSaloonRequest;
use Saloon\Http\Faking\SimulatedResponsePayload;

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
