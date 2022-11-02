<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;

trait HasCustomResponses
{
    /**
     * Define a custom response that the request will return.
     *
     * @var string|null
     */
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

        $baseResponseClass = $this instanceof SaloonRequest
            ? $this->connector()->sender()->getResponseClass()
            : $this->sender()->getResponseClass();

        if (empty($response) === true) {
            $response = $this instanceof SaloonRequest ? $this->connector()->getResponseClass() : $baseResponseClass;
        }

        if (class_exists($response) === false) {
            throw new SaloonInvalidResponseClassException;
        }

        $isValidResponse = ReflectionHelper::isSubclassOf($response, $baseResponseClass);

        if ($isValidResponse === false) {
            throw new SaloonInvalidResponseClassException(sprintf('The custom response provided must extend the "%s" class.', $baseResponseClass));
        }

        return $response;
    }
}
