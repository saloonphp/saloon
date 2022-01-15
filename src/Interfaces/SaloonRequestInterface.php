<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Http\SaloonConnector;

interface SaloonRequestInterface
{
    public function getMethod(): ?string;

    public function getConnector(): ?SaloonConnector;

    public function defineEndpoint(): string;

    public function defaultHeaders(): array;

    public function defaultConfig(): array;

    public function postData(): array;

    public function defineAuth(): array;

    public function interceptRequest(SaloonRequest $request): SaloonRequest;

    public function interceptResponse(SaloonRequest $request, SaloonResponse $responseInstance): SaloonResponse;

    public function defaultSuccessMockResponse(): void;

    public function defaultFailureMockResponse(): void;
}
