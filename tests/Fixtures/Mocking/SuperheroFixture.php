<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;

class SuperheroFixture extends Fixture
{
    protected function defineName(): string
    {
        return 'superhero';
    }

    protected function defineSensitiveJsonParameters(): array
    {
        return [
            'publisher' => 'REDACTED',
        ];
    }
}
