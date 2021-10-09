<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasBody
{
    public function bootHasBodyFeature()
    {
        $this->mergeConfig([
            'form_params' => $this->defineBody(),
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
