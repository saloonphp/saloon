<?php

namespace Sammyjo20\Saloon\Interfaces;

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
