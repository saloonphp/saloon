<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Keychains;

use Sammyjo20\Saloon\Helpers\Keychain;
use Sammyjo20\Saloon\Http\SaloonRequest;

class AdvancedKeychain extends Keychain
{
    /**
     * @param string $token
     * @param string $apiKey
     */
    public function __construct(
        protected string $token,
        protected string $apiKey,
    )
    {
        //
    }

    /**
     * @param SaloonRequest $request
     * @return Keychain
     */
    public static function default(SaloonRequest $request): Keychain
    {
        return new static('12345', 'my-api-key');
    }

    /**
     * @param SaloonRequest $request
     * @return void
     */
    public function boot(SaloonRequest $request): void
    {
        $request->withToken($this->token);
        $request->addHeader('X-API-Key', $this->apiKey);
    }
}
