<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;

class RegexUserFixture extends Fixture
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

    /**
     * Define regex patterns that should be replaced
     *
     * @return array|callable[]|string[]
     */
    protected function defineSensitiveRegexPatterns(): array
    {
        return [
            // Twitter Handle
            '/(\@)([a-z0-9_]{1,15})/i' => '**REDACTED-TWITTER**',
            // The name Sam
            '/Sam/' => 'Taylor',
        ];
    }
}
