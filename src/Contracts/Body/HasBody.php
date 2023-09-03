<?php

declare(strict_types=1);

namespace Saloon\Contracts\Body;

interface HasBody
{
    /**
     * Define Data
     */
    public function body(): BodyRepository;
}
