<?php

namespace Sammyjo20\Saloon\Managers;

use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;

class LaravelManger
{
    use CollectsHeaders,
        CollectsConfig,
        CollectsHandlers,
        CollectsInterceptors;

    /**
     * Is the Laravel app in mocking mode?
     *
     * @var bool
     */
    protected bool $isMocking = false;

    /**
     * Set if we are mocking or not
     *
     * @param bool $isMocking
     * @return $this
     */
    public function setIsMocking(bool $isMocking): self
    {
        $this->isMocking = $isMocking;

        return $this;
    }

    /**
     * Is the Laravel app in mocking mode?
     *
     * @return bool
     */
    public function isMocking(): bool
    {
        return $this->isMocking;
    }
}
