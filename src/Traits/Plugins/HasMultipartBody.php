<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasMultipartBody
{
    /**
     * @param SaloonRequest $request
     * @return void
     */
    public function bootHasMultipartBody(SaloonRequest $request): void
    {
        $this->addConfig('multipart', $this->getData());
    }

    /**
     * Check if the connector has a trait
     *
     * @return bool
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    protected function connectorHasDataTrait(): bool
    {
        return $this->traitExistsOnConnector(HasMultipartBody::class);
    }
}
