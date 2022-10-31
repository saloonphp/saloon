<?php

namespace Sammyjo20\Saloon\Interfaces\Data;

interface WithBody
{
    /**
     * Define Data
     *
     * @return BodyRepository
     */
    public function body(): BodyRepository;
}
