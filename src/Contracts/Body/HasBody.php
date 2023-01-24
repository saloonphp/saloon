<?php

declare(strict_types=1);

namespace Saloon\Contracts\Body;

interface HasBody
{
    /**
     * Define Data
     *
     * @return \Saloon\Contracts\Body\BodyRepository
     */
    public function body(): BodyRepository;
}
