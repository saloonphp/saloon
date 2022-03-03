<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Interfaces\KeychainInterface;

abstract class Keychain implements KeychainInterface
{
    /**
     * Specify default data that should be loaded if a keychain
     * is not specified on the request.
     *
     * @param SaloonRequest $request
     * @return Keychain
     */
    public static function default(SaloonRequest $request): Keychain
    {
        return new static();
    }
}
