<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Collections;

use Sammyjo20\Saloon\Http\Groups\RequestGroup;

class GuessedGroup extends RequestGroup
{
    public function test(): bool
    {
        // Has access to $this->connector

        return true;
    }
}
