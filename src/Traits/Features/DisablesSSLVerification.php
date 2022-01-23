<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait DisablesSSLVerification
{
    /**
     * Disable SSL verification on requests. I hope you know this is bad.
     *
     * @return void
     */
    public function bootDisablesSSLVerificationFeature()
    {
        $this->addConfig('verify', false);
    }
}
