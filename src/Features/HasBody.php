<?php

namespace Sammyjo20\Saloon\Features;

class HasBody extends SaloonFeature
{
    /**
     * Add form params to the request.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'form_params' => $this->request->defineBody(),
        ];
    }
}
