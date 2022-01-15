<?php

namespace Sammyjo20\Saloon\Traits;

use ReflectionClass;

trait ManagesFeatures
{
    /**
     * @var array
     */
    private array $features = [];

    /**
     * Load all the features.
     *
     * @throws \ReflectionException
     */
    private function loadFeatures(): void
    {
        // Check for the interfaces on the request class and see if we need to load
        // any options. E.g if they have the "hasBody" interface, we need to add the
        // body to the request.

        $connectorTraits = (new ReflectionClass($this->request->getConnector()))->getTraits();
        $requestTraits = (new ReflectionClass($this->request))->getTraits();

        $this->scanTraits($connectorTraits, 'connector')
            ->scanTraits($requestTraits, 'request');
    }

    /**
     * Scan through each of the traits and attempt to find the "boot" method.
     * If it exists, then we will run it.
     *
     * @param array $traits
     * @param string $type
     * @return $this
     */
    private function scanTraits(array $traits, string $type): self
    {
        foreach ($traits as $trait) {
            $featureName = $trait->getShortName();

            if (in_array($featureName, $this->features, true)) {
                continue;
            }

            $bootName = 'boot' . $featureName . 'Feature';

            if ($trait->hasMethod($bootName) === false) {
                continue;
            }

            if ($type !== 'connector' && $type !== 'request') {
                continue;
            }

            if ($type === 'connector' && method_exists($this->connector, $bootName)) {
                $this->bootConnectorFeature($bootName);
            }

            if ($type === 'request' && method_exists($this->request, $bootName)) {
                $this->bootRequestFeature($bootName);
            }

            $this->features[] = $featureName . $type;
        }

        return $this;
    }

    /**
     * Run the method on the connector.
     *
     * @param string $methodName
     */
    private function bootConnectorFeature(string $methodName): void
    {
        $this->connector->{$methodName}();
    }

    /**
     * Run the boot method on the request.
     *
     * @param string $methodName
     */
    private function bootRequestFeature(string $methodName): void
    {
        $this->request->{$methodName}();
    }
}
