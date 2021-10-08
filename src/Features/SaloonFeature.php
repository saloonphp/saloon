<?php

namespace Sammyjo20\Saloon\Features;

use Sammyjo20\Saloon\Interfaces\SaloonFeatureInterface;
use Sammyjo20\Saloon\Http\SaloonRequest;

abstract class SaloonFeature implements SaloonFeatureInterface
{
    protected SaloonRequest $request;

    /**
     * @param SaloonRequest $request
     */
    public function __construct(SaloonRequest $request)
    {
        $this->request = $request;
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function getConfig(): array
    {
        return [];
    }
}
