<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait AcceptsJson
{
    public function bootAcceptsJsonFeature()
    {
        $this->mergeHeaders([
            'Accept' => 'application/json',
        ]);
    }
}
