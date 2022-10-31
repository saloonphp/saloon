<?php

namespace Sammyjo20\Saloon\Contracts\Body;

interface WithBody
{
    /**
     * Define Data
     *
     * @return BodyRepository
     */
    public function body(): BodyRepository;
}
