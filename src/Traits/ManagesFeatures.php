<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Interfaces\Request\HasJsonBody as HasJsonBodyInterface;
use Sammyjo20\Saloon\Features\HasJsonBody as HasJsonBodyFeature;
use Sammyjo20\Saloon\Interfaces\Request\AcceptsJson as AcceptsJsonInterface;
use Sammyjo20\Saloon\Features\AcceptsJson as AcceptsJsonFeature;
use Sammyjo20\Saloon\Interfaces\Request\HasBody as HasBodyInterface;
use Sammyjo20\Saloon\Features\HasBody as HasBodyFeature;
use Sammyjo20\Saloon\Features\SaloonFeature;
use ReflectionClass;

trait ManagesFeatures
{
    /**
     * These are all the features.
     *
     * @var array|string[]
     */
    private array $featureMapper = [
        HasBodyInterface::class => HasBodyFeature::class,
        AcceptsJsonInterface::class => AcceptsJsonFeature::class,
        HasJsonBodyInterface::class => HasJsonBodyFeature::class,
    ];

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

        $connectorInterfaces = (new ReflectionClass($this->request->getConnector()))->getInterfaceNames();
        $requestInterfaces = (new ReflectionClass($this->request))->getInterfaceNames();

        $interfaces = array_merge($connectorInterfaces, $requestInterfaces);

        foreach ($interfaces as $interface) {
            if (! array_key_exists($interface, $this->featureMapper)) {
                continue;
            }

            $feature = $this->featureMapper[$interface];

            if (in_array($feature, $this->features, true)) {
                continue;
            }

            $this->features[] = $feature;

            $this->bootFeature(new $feature($this->request));
        }
    }

    /**
     * Boot each filter by adding the headers.
     *
     * @param SaloonFeature $feature
     */
    private function bootFeature(SaloonFeature $feature): void
    {
        foreach ($feature->getHeaders() as $header => $value) {
            $this->addHeader($header, $value);
        }

        foreach ($feature->getConfig() as $option => $value) {
            $this->addConfigVariable($option, $value);
        }
    }
}
