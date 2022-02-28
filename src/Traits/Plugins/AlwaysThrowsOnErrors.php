<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;

trait AlwaysThrowsOnErrors
{
    /**
     * Always throw if there is something wrong with the request.
     *
     * @param SaloonRequest $request
     * @return void
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootAlwaysThrowsOnErrors(SaloonRequest $request): void
    {
        if ($this instanceof SaloonRequest && $this->traitExistsOnConnector(AlwaysThrowsOnErrors::class)) {
            return;
        }

        $this->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
            $response->throw();

            return $response;
        });
    }
}
