<?php declare(strict_types=1);

namespace Saloon\Http\Groups;

use Saloon\Http\SaloonConnector;
use Saloon\Helpers\RequestHelper;
use Saloon\Exceptions\NestedRequestNotFoundException;

class AnonymousRequestGroup extends RequestGroup
{
    /**
     * Constructor
     *
     * @param SaloonConnector $connector
     * @param string $collectionName
     * @param array $requests
     */
    public function __construct(
        SaloonConnector $connector,
        protected string $collectionName,
        protected array $requests,
    ) {
        parent::__construct($connector);
    }

    /**
     * Invoke a request from a connector.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws NestedRequestNotFoundException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException
     */
    public function __call(string $name, array $arguments)
    {
        if (! array_key_exists($name, $this->requests)) {
            throw new NestedRequestNotFoundException($name, $this->collectionName, $this->connector);
        }

        return RequestHelper::callFromConnector($this->connector, $this->requests[$name], $arguments);
    }
}
