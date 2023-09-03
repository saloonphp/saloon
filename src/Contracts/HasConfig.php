<?php

declare(strict_types=1);

namespace Saloon\Contracts;

/**
 * @internal
 */
interface HasConfig
{
    /**
     * Access the config
     *
     * @return \Saloon\Contracts\ArrayStore
     */
    public function config(): ArrayStore;
}
