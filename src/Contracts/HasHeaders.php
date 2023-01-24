<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface HasHeaders
{
    /**
     * Access the headers
     *
     * @return \Saloon\Contracts\ArrayStore
     */
    public function headers(): ArrayStore;
}
