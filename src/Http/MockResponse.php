<?php

namespace Sammyjo20\Saloon\Http;

use GuzzleHttp\Psr7\Response;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\Features\HasBody;

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
     * @var mixed
     */
    protected mixed $rawData = null;

    /**
     * Create a new mock response
     *
     * @param int $status
     * @param array $data
     * @param array $headers
     * @param array $config
     */
    public function __construct(mixed $data = [], int $status = 200, array $headers = [], array $config = [])
    {
        $this->status = $status;

        // If the data type is an array, we'll assume that it should be JSON data.
        // of course - if content-type is passed into $headers, it will replace this
        // default.

        if (is_array($data)) {
            $this->mergeData($data)->addHeader('Content-Type', 'application/json');
        } else {
            $this->rawData = $data;
        }

        $this->mergeHeaders($headers)->mergeConfig($config);
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
        // Let's try to work out where we should pull in the data in. If the request uses
        // the HasBody trait, that means it's going to be raw data - so we'll just grab that
        // raw data. Otherwise, use the normal "getData" method.

        $data = array_key_exists(HasBody::class, class_uses($request)) && method_exists($request, 'defineBody')
            ? $request->defineBody()
            : $request->getData();

        return new static($data, $status, $request->getHeaders(), $request->getConfig());
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

    /**
     * Get the formatted data on the response.
     *
     * @return mixed
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function getFormattedData(): mixed
    {
        if (isset($this->rawData)) {
            return $this->rawData;
        }

        $data = $this->getData();

        if (is_array($data) && $this->getHeader('Content-Type') == 'application/json') {
            return json_encode($data);
        }

        return $data;
    }

    /**
     * Convert the mock response into a Guzzle response
     *
     * @return Response
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function toGuzzleResponse(): Response
    {
        return new Response($this->getStatus(), $this->getHeaders(), $this->getFormattedData());
    }
}
