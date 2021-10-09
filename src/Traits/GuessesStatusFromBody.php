<?php

namespace Sammyjo20\Saloon\Traits;

trait GuessesStatusFromBody
{
    /**
     * Is it one of those APIs that says 200 but has a "status" in the body? Use this
     * and we'll attempt to guess the code from that.
     *
     * https://imgur.com/a/b3522Hk
     *
     * @var bool
     */
    public bool $shouldGuessStatusFromBody = true;
}
