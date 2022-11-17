<?php declare(strict_types=1);

namespace Saloon\Traits\Responses;

use ReflectionException;
use Saloon\Http\Request;
use Saloon\Helpers\ReflectionHelper;
use Saloon\Exceptions\InvalidConnectorException;
use Saloon\Exceptions\InvalidResponseClassException;

trait HasCustomResponses
{
    /**
     * Specify a default response.
     *
     * When an empty string, the response on the sender will be used.
     *
     * @var string
     */
    protected string $response = '';

    /**
     * Get the response class
     *
     * @return string
     * @throws ReflectionException
     * @throws InvalidConnectorException
     * @throws InvalidResponseClassException
     */
    public function getResponseClass(): string
    {
        $baseResponse = $this->sender()->getResponseClass();
        $response = $this->resolveResponse();

        if (empty($response)) {
            $response = $this instanceof Request ? $this->connector()->getResponseClass() : $baseResponse;
        }

        if (! class_exists($response)) {
            throw new InvalidResponseClassException;
        }

        if (! ReflectionHelper::isSubclassOf($response, $baseResponse)) {
            throw new InvalidResponseClassException(sprintf('The custom response must extend the "%s" class.', $baseResponse));
        }

        return $response;
    }

    /**
     * Resolve the custom response class
     *
     * @return string
     */
    protected function resolveResponse(): string
    {
        return $this->response;
    }
}
