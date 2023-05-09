<?php

declare(strict_types=1);

namespace Saloon\Contracts\Body;

/**
 * @deprecated 3.0.0 You are no longer required to provide the HasBody trait when using request body.
 * This interface will be removed in future versions of Saloon.
 */
interface HasBody
{
    /**
     * Define Data
     *
     * @return \Saloon\Contracts\Body\BodyRepository
     */
    public function body(): BodyRepository;
}
