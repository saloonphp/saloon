<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\SaloonResponse;
use GuzzleHttp\Psr7\Request;

interface SaloonConnectorInterface
{
    public function defineBaseUrl(): string;

    public function defaultHeaders(): array;

    public function defaultConfig(): array;

    public function defineAuth(): array;

    public function interceptRequest(Request $requestInstance): Request;

    public function interceptResponse($requestInstance, SaloonResponse $responseInstance): SaloonResponse;
}
