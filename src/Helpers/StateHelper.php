<?php

declare(strict_types=1);

namespace Saloon\Helpers;

class StateHelper
{
    /**
     * Generate a random string for the state.
     *
     * @return string
     * @throws \Exception
     */
    public static function createRandomState(): string
    {
        return Str::random(32);
    }
}
