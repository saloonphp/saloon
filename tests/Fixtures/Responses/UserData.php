<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Responses;

class UserData
{
    /**
     * CustomAbstractResponse constructor.
     * @param string $foo
     */
    public function __construct(
        public string $foo
    ) {
        // ..
    }
}
