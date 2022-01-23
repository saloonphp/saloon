<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\SaloonConnector;

interface SaloonRequestInterface
{
    public function getMethod(): ?string;

    public function getConnector(): ?SaloonConnector;

    public function defineEndpoint(): string;

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
