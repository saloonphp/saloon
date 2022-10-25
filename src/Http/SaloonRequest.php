<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\BuildsUrls;
use Sammyjo20\Saloon\Traits\HasConnector;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\SendsRequests;
use Sammyjo20\Saloon\Traits\CastsResponseToDto;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;

abstract class SaloonRequest
{
    use HasRequestProperties;
    use AuthenticatesRequests;
    use HasCustomResponses;
    use MocksRequests;
    use SendsRequests;
    use BuildsUrls;
    use CastsResponseToDto;
    use HasConnector;

    /**
     * Define the connector.
     *
     * @var string
     */
    protected string $connector = '';

    /**
     * Define the method.
     *
     * @var string
     */
    protected string $method = '';

    /**
     * Denotes if the request is being used to record a fixture.
     *
     * @var bool
     */
    protected bool $isRecordingFixture = false;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    abstract protected function defineEndpoint(): string;

    /**
     * Handle the booting of a request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function boot(PendingSaloonRequest $pendingRequest): void
    {
        //
    }

    /**
     * Create the request payload which will run all plugins, boot methods, everything.
     *
     * @return PendingSaloonRequest
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\DataBagException
     * @throws \Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException
     */
    public function createPendingRequest(): PendingSaloonRequest
    {
        return new PendingSaloonRequest($this);
    }

    /**
     * Get the method of the request.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Instantiate a new class with the arguments.
     *
     * @param ...$arguments
     * @return static
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    /**
     * Set if the request is being used to record a fixture.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsRecordingFixture(bool $value): static
    {
        $this->isRecordingFixture = $value;

        return $this;
    }

    /**
     * Get if the request is recording a fixture.
     *
     * @return bool
     */
    public function isRecordingFixture(): bool
    {
        return $this->isRecordingFixture;
    }

    /**
     * Get if the request is not recording a fixture.
     *
     * @return bool
     */
    public function isNotRecordingFixture(): bool
    {
        return ! $this->isRecordingFixture();
    }

    /**
     * Dynamically proxy other methods to the connector.
     *
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws SaloonMethodNotFoundException
     */
    public function __call($method, $parameters)
    {
        $connector = $this->getConnector();

        if (method_exists($connector, $method) === false) {
            throw new SaloonMethodNotFoundException($method, $connector);
        }

        return $connector->{$method}(...$parameters);
    }
}
