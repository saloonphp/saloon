<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;

class RegexUserFixture extends Fixture
{
    /**
     * Define the name of the fixture
     */
    protected function defineName(): string
    {
        return 'user';
    }

    /**
     * Define regex patterns that should be replaced
     *
     * @return array|callable[]|string[]
     */
    protected function defineSensitiveRegexPatterns(): array
    {
        return [
            // Twitter Handle
            '/@[a-z0-9_]{0,100}/' => '**REDACTED-TWITTER**',
            // The name Sam
            '/Sam/' => fn (string $value) => substr_replace($value, 'xxx', 1),
        ];
    }
}
