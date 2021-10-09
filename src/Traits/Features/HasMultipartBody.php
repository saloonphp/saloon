<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasMultipartBody
{
    public function bootHasMultipartBodyFeature()
    {
        $this->mergeConfig([
            'multipart' => $this->defineMultipartBody(),
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
