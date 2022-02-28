<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasBody
{
    /**
     * Define any form body.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function bootHasBody(SaloonRequest $request): void
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
