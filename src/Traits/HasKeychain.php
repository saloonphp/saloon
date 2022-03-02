<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Helpers\Keychain;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;

trait HasKeychain
{
    /**
     * The class string for a Saloon keychain.
     *
     * @var string|null
     */
    protected ?string $defaultKeychain = null;

    /**
     * The loaded keychain if passed into the request/connector.
     *
     * @var Keychain|null
     */
    private ?Keychain $loadedKeychain = null;

    /**
     * Boot up the keychain if it is provided.
     *
     * @param SaloonRequest $request
     * @return void
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootKeychain(SaloonRequest $request): void
    {
        // Let's get the keychain from the request/connector. This method
        // will return an instance of keychain or null. If someone hasn't
        // loaded a keychain manually but a default keychain was provided
        // we will load that one.

        $keychain = $this->getKeychain($request) ?? $this->getConnector()->getKeychain($request);

        if (! $keychain instanceof Keychain) {
            return;
        }

        // If we have a keychain class, we should authenticate the request
        // which is useful for debugging. After that, we will run the "boot"
        // method on the keychain which applies changes to the request.

        $this->authenticate($keychain);

        $keychain->boot($request);
    }

    /**
     * Attempt to find a keychain on the class. If a loaded keychain
     * already exists, we will return that otherwise we will check
     * if the class has a default connector and create one if
     * it exits.
     *
     * @param SaloonRequest $request
     * @return Keychain|null
     * @throws \ReflectionException
     */
    public function getKeychain(SaloonRequest $request): ?Keychain
    {
        if ($this->loadedKeychain instanceof Keychain) {
            return $this->loadedKeychain;
        }

        if ($this->hasDefaultKeychain()) {
            return $this->defaultKeychain::default($request);
        }

        return null;
    }

    /**
     * Retrieve the loaded keychain.
     *
     * @return Keychain|null
     */
    public function getLoadedKeychain(): ?Keychain
    {
        return $this->loadedKeychain;
    }

    /**
     * Authenticate the class with a keychain.
     *
     * @param Keychain $keychain
     * @return $this
     */
    public function authenticate(Keychain $keychain): self
    {
        $this->loadedKeychain = $keychain;

        return $this;
    }

    /**
     * Check if we have a default keychain defined.
     *
     * @return bool
     * @throws \ReflectionException
     */
    private function hasDefaultKeychain(): bool
    {
        $keychain = $this->defaultKeychain;

        return is_string($keychain)
            && class_exists($keychain)
            && ReflectionHelper::isSubclassOf($keychain, Keychain::class);
    }
}
