<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\SaloonResponse;
use GuzzleHttp\Psr7\Request;

interface SaloonRequestInterface
{
    public function defineMethod(): ?string;

    public function getConnector(): ?SaloonConnector;

    public function defineEndpoint(): string;

    public function defaultHeaders(): array;

    public function defaultConfig(): array;

    public function defineData(): array;

    public function defineAuth(): array;

    public function interceptRequest(Request $requestInstance): Request;

    public function interceptResponse($requestInstance, SaloonResponse $responseInstance): SaloonResponse;

    public function mockSuccessResponse(): array;

    public function mockFailureResponse(): array;
}
