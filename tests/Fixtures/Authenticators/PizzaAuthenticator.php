<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Authenticators;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;

class PizzaAuthenticator implements AuthenticatorInterface
{
    /**
     * @param string $pizza
     * @param string $drink
     */
    public function __construct(
        public string $pizza,
        public string $drink,
    ) {
        //
    }

    /**
     * @param SaloonRequest $request
     * @return void
     */
    public function set(SaloonRequest $request): void
    {
        $request->addHeader('X-Pizza', $this->pizza);
        $request->addHeader('X-Drink', $this->drink);

        $request->addConfig('debug', true);
    }
}
