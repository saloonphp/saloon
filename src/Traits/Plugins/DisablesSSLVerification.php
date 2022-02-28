<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

trait DisablesSSLVerification
{
    /**
     * Disable SSL verification on requests. I hope you know this is bad.
     *
     * @return void
     */
    public function bootDisablesSSLVerification(): void
    {
        $this->addConfig('verify', false);
    }
}
