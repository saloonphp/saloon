<?php

namespace Sammyjo20\Saloon\Traits\Features;

use \ReflectionClass;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;

trait HasResponseClass
{
    protected ?string $response = null;

    /**
     * Get the response class
     *
     * @return string
     * @throws \ReflectionException
     * @throws SaloonInvalidConnectorException
     * @throws SaloonInvalidResponseClassException
     */
    public function getResponseClass(): string
    {
        $response = $this->response;

        if (empty($response)) {
            $response = $this instanceof SaloonRequest ? $this->getConnector()->getResponseClass() : SaloonResponse::class;
        }

        if (! class_exists($response)) {
            throw new SaloonInvalidResponseClassException;
        }

        $isValidResponse = $response === SaloonResponse::class
            || (new ReflectionClass($response))->isSubclassOf(SaloonResponse::class);

        if (! $isValidResponse) {
            throw new SaloonInvalidResponseClassException;
        }

        return $response;
    }
}
