<?php

namespace Sammyjo20\Saloon\Features;

class HasJsonBody extends SaloonFeature
{
    /**
     * Set the correct content type.
     *
     * @return string[]
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Add form params to the request.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'json' => $this->request->defineBody(),
        ];
    }
}
