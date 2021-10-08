<?php

namespace Sammyjo20\Saloon\Features;

class AcceptsJson extends SaloonFeature
{
    /**
     * Add form params to the request.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Accept' => 'application/json'
        ];
    }
}
