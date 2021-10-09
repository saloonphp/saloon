<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasJsonBody
{
    public function bootHasJsonBodyFeature()
    {
        $this->mergeHeaders([
            'Content-Type' => 'application/json'
        ]);

        $this->mergeConfig([
            'json' => $this->defineBody(),
        ]);
    }

    public function defineBody(): array
    {
        if ($this instanceof SaloonRequest) {
            return $this->getData();
        }

        return [];
    }
}
