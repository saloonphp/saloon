<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Keychains;

use Sammyjo20\Saloon\Helpers\Keychain;
use Sammyjo20\Saloon\Http\SaloonRequest;

class AuthKeychain extends Keychain
{
    /**
     * @param string $token
     */
    public function __construct(
        protected string $token,
    ) {
        //
    }

    /**
     * @param SaloonRequest $request
     * @return Keychain
     */
    public static function default(SaloonRequest $request): Keychain
    {
        return new static('12345');
    }

    /**
     * @param SaloonRequest $request
     * @return void
     */
    public function boot(SaloonRequest $request): void
    {
        $request->withToken($this->token);
    }
}
