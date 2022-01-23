<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasQueryParams
{
    public function bootHasQueryParamsFeature()
    {
        $this->addConfig('query', $this->getQuery());
    }
}
