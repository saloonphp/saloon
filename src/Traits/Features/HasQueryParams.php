<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasQueryParams
{
    public function bootHasQueryParamsFeature()
    {
        $this->mergeConfig([
            'query' => $this->getQuery(),
        ]);
    }
}
