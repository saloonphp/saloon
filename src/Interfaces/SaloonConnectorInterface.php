<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\SaloonRequest;

interface SaloonConnectorInterface
{
    public function boot(SaloonRequest $request): void;

    public function defineBaseUrl(): string;

    public function defaultHeaders(): array;

    public function defaultConfig(): array;

    public function getResponseClass(): string;

    public function defaultData(): array;

    public function defaultQuery(): array;

    public function addHandler(string $name, callable $function): void;

    public function getHandlers(): array;

    public function addResponseInterceptor(callable $function): void;

    public function getResponseInterceptors(): array;

    public function getRegisteredRequests(): array;

    public function requestExists(string $method): bool;
}
