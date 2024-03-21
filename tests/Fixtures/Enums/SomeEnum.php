<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Enums;

enum SomeEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}
