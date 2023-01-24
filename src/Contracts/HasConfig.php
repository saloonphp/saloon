<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface HasConfig
{
    /**
     * Access the config
     *
     * @return \Saloon\Contracts\ArrayStore
     */
    public function config(): ArrayStore;
}
