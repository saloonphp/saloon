<?php

namespace Sammyjo20\Saloon\Helpers;

use Illuminate\Support\Str;

class StateHelper
{
    /**
     * Generate a random string for the state.
     *
     * @return string
     */
    public static function createRandomState(): string
    {
        return Str::random(32);
    }
}
