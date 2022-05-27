<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Http\RequestPayload;
use Sammyjo20\Saloon\Http\SaloonConnector;

interface SaloonRequestInterface
{
    public function beforeSend(RequestPayload $payload): void;

    public function getMethod(): Method;

    public function getResponseClass(): ?string;

    public function getConnector(): ?SaloonConnector;

    public function setConnector(SaloonConnector $connector): self;
}
