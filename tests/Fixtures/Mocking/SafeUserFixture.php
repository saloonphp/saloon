<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;

class SafeUserFixture extends Fixture
{
    /**
     * Define the name of the fixture
     */
    protected function defineName(): string
    {
        return 'user';
    }

    /**
     * Define the sensitive headers
     */
    protected function defineSensitiveHeaders(): array
    {
        return [
            'Server' => 'secret',

            // Check for case-insensitive
            'cache-control' => function ($value) {
                return $value . ', yeehaw';
            },
        ];
    }

    /**
     * Swap any sensitive JSON parameters
     */
    protected function defineSensitiveJsonParameters(): array
    {
        return [
            // You can also define callables that should be run to replace the value!

            'name' => static function (string $value) {
                return substr_replace($value, 'xxx', 1);
            },
            'actual_name' => 'REDACTED',
            'twitter' => '@saloonphp',
        ];
    }
}
