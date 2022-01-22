<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsHeaders;

class MockResponse
{
    use CollectsHeaders,
        CollectsConfig,
        CollectsData;

    /**
     * @var int
     */
    protected int $status;

    /**
     * Create a new mock response
     *
     * @param int $status
     * @param array $data
     * @param array $headers
     * @param array $config
     */
    public function __construct(array $data = [], int $status = 200, array $headers = [], array $config = [])
    {
        $this->status = $status;

        $this->mergeData($data)->mergeHeaders($headers)->mergeConfig($config);
    }

    /**
     * Create a new mock response from a Saloon request.
     *
     * @param SaloonRequest $request
     * @param int $status
     * @return static
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public static function fromRequest(SaloonRequest $request, int $status = 200): self
    {
        return new static($request->getData(), $status, $request->getHeaders(), $request->getConfig());
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

    public function getFormattedData()
    {

    }
}
