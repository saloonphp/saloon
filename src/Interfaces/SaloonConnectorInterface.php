<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;

interface SaloonConnectorInterface
{
    public function defineBaseUrl(): string;

    public function defaultHeaders(): array;

    public function defaultConfig(): array;

    public function defaultData(): array;

    public function defaultQuery(): array;

    public function addHandler(string $name, callable $function): void;

    public function getHandlers(): array;

    public function addResponseInterceptor(callable $function): void;

    public function getResponseInterceptors(): array;

    public function boot(): void;
}
