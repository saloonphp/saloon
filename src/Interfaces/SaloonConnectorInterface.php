<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\RequestPayload;
use Sammyjo20\Saloon\Http\SaloonRequest;

interface SaloonConnectorInterface
{
    public function beforeSend(RequestPayload $requestPayload): void;

    public function getResponseClass(): string;
}
