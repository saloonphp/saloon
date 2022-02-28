<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait DisablesSSLVerification
{
    /**
     * Disable SSL verification on requests. I hope you know this is bad.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function bootDisablesSSLVerification(SaloonRequest $request): void
    {
        $this->addConfig('verify', false);
    }
}
