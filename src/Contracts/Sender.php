<?php declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Http\PendingSaloonRequest;
use Saloon\Http\Responses\SaloonResponse;

interface Sender
{
    /**
     * Get the sender's response class
     *
     * @return string
     */
    public function getResponseClass(): string;

    /**
     * Send the request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     */
    public function sendRequest(PendingSaloonRequest $pendingRequest, bool $asynchronous = false): SaloonResponse|PromiseInterface;
}
