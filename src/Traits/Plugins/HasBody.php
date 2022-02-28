<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

trait HasBody
{
    /**
     * Define any form body.
     *
     * @return void
     */
    public function bootHasBody(): void
    {
        $this->addConfig('body', $this->defineBody());
    }

    /**
     * Define the body data that should be sent
     *
     * @return mixed
     */
    abstract public function defineBody(): mixed;
}
