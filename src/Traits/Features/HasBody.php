<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Exceptions\SaloonHasBodyException;

trait HasBody
{
    /**
     * Define any form body.
     *
     * @return void
     * @throws SaloonHasBodyException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootHasBodyFeature(): void
    {
        if ($this->traitExistsOnConnector(HasBody::class)) {
            throw new SaloonHasBodyException('You can not have the HasBody trait on both the request and the connector at the same time.');
        }

        $this->addConfig('body', $this->defineBody());
    }

    /**
     * Define the body data that should be sent
     *
     * @return mixed
     */
    public function defineBody(): mixed
    {
        return null;
    }
}
