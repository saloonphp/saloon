<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

trait AcceptsJson
{
    /**
     * @return void
     */
    public function bootAcceptsJson(): void
    {
        $this->addHeader('Accept', 'application/json');
    }
}
