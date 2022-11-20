<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Collections;

use Saloon\Http\Groups\RequestGroup;

class GuessedGroup extends RequestGroup
{
    public function test(): bool
    {
        // Has access to $this->connector

        return true;
    }
}
