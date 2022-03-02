<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Helpers\Keychain;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;

trait HasKeychain
{
    /**
     * The class for a Saloon keychain.
     *
     * @var string|null
     */
    protected ?string $defaultKeychain = null;

    /**
     * The preloaded keychain if passed into the request/connector.
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
        // Let's firstly get the keychain from the request/connector.
        // This method will return an instance of keychain or null.
        // If someone hasn't loaded a keychain manually, we will boot
        // one up.

        $keychain = $this->getKeychain($request) ?? $this->getConnector()->getKeychain($request);

        // If there is no keychain, it will just stop here.

        if (! $keychain instanceof Keychain) {
            return;
        }

        $this->authenticate($keychain);

        // If the keychain is valid, we should run the "boot" method on the keychain
        // which will let the keychain modify the request how it likes.

        $keychain->boot($request);
    }

    /**
     * Retrieve the loaded keychain from the request/connector.
     *
     * @return Keychain|null
     */
    public function getDefaultKeychain(): ?string
    {
        return $this->defaultKeychain;
    }

    /**
     * Load the keychain if there is not one already set.
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
     * Authenticate the request/connector with a keychain.
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
        $default = $this->defaultKeychain;

        return is_string($default)
            && class_exists($default)
            && ReflectionHelper::isSubclassOf($default, Keychain::class);
    }
}
