<?php

namespace Sammyjo20\Saloon\Helpers;

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
        $string = '';
        $length = 32;

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}
