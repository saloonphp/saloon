<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;

class UserFixture extends Fixture
{
    /**
     * Define the name of the fixture
     *
     * @return string
     */
    protected function defineName(): string
    {
        return 'user';
    }
}
