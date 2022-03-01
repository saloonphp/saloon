<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Helpers\Keychain;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;
use Sammyjo20\Saloon\Http\SaloonRequest;

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
        $keychain = $this->getLoadedKeychain();

        // If there is no loaded keychain, lets see if there is a loaded keychain on the connector.

        if (is_null($keychain) && $this instanceof SaloonRequest) {
            $keychain = $this->getConnector()->getLoadedKeychain();
        }

        // If there still is no loaded connector, lets see if there is a default keychain.
        // If there is, and it is a valid keychain class - we will run the "default"
        // method to populate the keychain.

        if (is_null($keychain)) {
            $defaultKeychain = $this->getDefaultKeychain();

            if (is_null($defaultKeychain) && $this instanceof SaloonRequest) {
                $defaultKeychain = $this->getConnector()->getDefaultKeychain();
            }

            if (is_null($defaultKeychain)) {
                return;
            }

            $isValidKeychain = ReflectionHelper::isSubclassOf($defaultKeychain, Keychain::class);

            if ($isValidKeychain === true) {
                $keychain = $defaultKeychain::default($request);
            }
        }

        // If the keychain is valid, we should run the "boot" method on the keychain
        // which will let the keychain modify the request how it likes.

        if ($keychain instanceof Keychain) {
            $this->loadedKeychain = $keychain;
            $keychain->boot($request);
        }
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
     * Retrieve the loaded keychain from the request/connector.
     *
     * @return Keychain|null
     */
    public function getLoadedKeychain(): ?Keychain
    {
        return $this->loadedKeychain;
    }

    /**
     * Specify a keychain to be used on the request/connector.
     *
     * @param Keychain $keychain
     * @return $this
     */
    public function withKeychain(Keychain $keychain): self
    {
        $this->loadedKeychain = $keychain;

        return $this;
    }
}
