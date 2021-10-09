<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait WithDebugData
{
    public function bootWithDebugDataFeature()
    {
        $this->mergeConfig([
            'debug' => true,
        ]);
    }
}
