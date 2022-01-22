<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;

class MockResponse
{
    use CollectsHeaders,
        CollectsConfig;

    /**
     * @var int
     */
    protected int $status;

    /**
     * @param int $status
     * @param array $headers
     * @param array $config
     */
    public function __construct(int $status, array $headers, array $config)
    {
        $this->status = $status;

        $this->mergeHeaders($headers)->mergeConfig($config);

        // Todo: Maybe add response interceptors too?   
    }

    /**
     * Create a new mock response from a Saloon request.
     *
     * @param int $status
     * @param SaloonRequest $request
     * @return MockResponse
     */
    public static function fromRequest(int $status, SaloonRequest $request): self
    {
        return new static($status, $request->getHeaders(), $request->getConfig());
    }

    /**
     * Get the status from the responses
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}
