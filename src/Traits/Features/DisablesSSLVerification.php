<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait DisablesSSLVerification
{
    public function bootDisablesSSLVerificationFeature()
    {
        // I hope you know that this is bad.

        $this->mergeConfig([
            'verify' => false,
        ]);
    }
}
