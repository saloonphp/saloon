<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Responses;

class UserData
{
    /**
     * CustomResponse constructor.
     * @param string $foo
     */
    public function __construct(
        public string $foo
    ) {
        // ..
    }
}
