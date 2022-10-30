<?php

namespace Sammyjo20\Saloon\Interfaces\Data;

use Sammyjo20\Saloon\Data\RequestBodyType;

interface WithBody
{
    /**
     * Define the body type
     *
     * @return RequestBodyType
     */
    public function getBodyType(): RequestBodyType;

    /**
     * Define Data
     *
     * @return BodyRepository
     */
    public function body(): BodyRepository;
}
