<?php

namespace Sammyjo20\Saloon\Interfaces;

use GuzzleHttp\Psr7\Request;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;

interface SaloonConnectorInterface
{
    public function defineBaseUrl(): string;

    public function defaultHeaders(): array;

    public function defaultConfig(): array;

    public function defineAuth(): array;

    public function postData(): array;

    public function interceptRequest(SaloonRequest $request): SaloonRequest;

    public function interceptResponse(SaloonRequest $request, SaloonResponse $responseInstance): SaloonResponse;
}
