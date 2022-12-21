<?php

declare(strict_types=1);

namespace Saloon\Contracts\Body;

interface HasBody
{
    /**
     * Define Data
     *
     * @return BodyRepository
     */
    public function body(): BodyRepository;
}
