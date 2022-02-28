<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait AcceptsJson
{
    /**
     * @param SaloonRequest $request
     * @return void
     */
    public function bootAcceptsJson(SaloonRequest $request): void
    {
        $this->addHeader('Accept', 'application/json');
    }
}
