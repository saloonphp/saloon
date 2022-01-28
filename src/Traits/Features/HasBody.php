<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasBody
{
    /**
     * Define any form body.
     *
     * @return void
     */
    public function bootHasBodyFeature(): void
    {
        $this->addConfig('body', $this->defineBody());
    }

    /**
     * Define the body data that should be sent
     *
     * @return mixed
     */
    public function defineBody(): mixed
    {
        return null;
    }
}
