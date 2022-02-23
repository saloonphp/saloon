<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait AcceptsJson
{
    public function bootAcceptsJson()
    {
        $this->addHeader('Accept', 'application/json');
    }
}
