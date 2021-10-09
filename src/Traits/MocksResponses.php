<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Exceptions\SaloonMissingMockException;
use Sammyjo20\Saloon\Http\SaloonMock;

trait MocksResponses
{
    protected ?SaloonMock $successMock = null;

    protected ?SaloonMock $failureMock = null;

    public function setSuccessMockResponse(int $statusCode, array $headers = [], string|array $body = ''): void
    {
        $this->successMock = new SaloonMock($statusCode, $headers, $body);
    }

    public function setFailureMockResponse(int $statusCode, array $headers = [], string|array $body = ''): void
    {
        $this->failureMock = new SaloonMock($statusCode, $headers, $body);
    }

    public function getSuccessMock(): SaloonMock
    {
        if (! $this->successMock instanceof SaloonMock) {
            $this->defaultSuccessMockResponse();
        }

        if (! $this->successMock instanceof SaloonMock) {
            throw new SaloonMissingMockException('You have not defined a "success" mock for this request. Please set one with the "setSuccessMockResponse" method.');
        }

        return $this->successMock;
    }

    public function getFailureMock(): SaloonMock
    {
        if (! $this->failureMock instanceof SaloonMock) {
            $this->defaultFailureMockResponse();
        }

        if (! $this->failureMock instanceof SaloonMock) {
            throw new SaloonMissingMockException('You have not defined a "failure" mock for this request. Please set one with the "setFailureMockResponse" method.');
        }

        return $this->failureMock;
    }
}
