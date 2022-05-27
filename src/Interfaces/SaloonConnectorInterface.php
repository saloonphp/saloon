<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\SaloonRequest;

interface SaloonConnectorInterface
{
    public function boot(SaloonRequest $request): void;

    public function defineBaseUrl(): string;

    public function getResponseClass(): string;

    public function getRegisteredRequests(): array;

    public function requestExists(string $method): bool;
}
