<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasMultipartBody
{
    public function bootHasMultipartBodyFeature()
    {
        $this->mergeConfig([
            'multipart' => $this->allData(),
        ]);
    }
}
