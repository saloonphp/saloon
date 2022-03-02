<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Helpers\Keychain;
use Sammyjo20\Saloon\Http\SaloonRequest;

interface KeychainInterface
{
    /**
     * Specify default data that should be loaded if a keychain
     * is not specified on the request.
     *
     * @param SaloonRequest $request
     * @return Keychain
     */
    public static function default(SaloonRequest $request): Keychain;

    /**
     * Use the data inside the keychain to modify the request.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function boot(SaloonRequest $request): void;
}
