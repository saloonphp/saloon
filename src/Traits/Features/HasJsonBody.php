<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasJsonBody
{
    public function bootHasJsonBodyFeature()
    {
        $this->mergeHeaders([
            'Content-Type' => 'application/json',
        ]);

        $this->mergeConfig([
            'json' => $this->allData(),
        ]);
    }

    /**
     *
     *
     * @return array
     */
    private function jsonBodyData(): array
    {
        if ($this instanceof SaloonRequest && method_exists($this->connector, 'bootHasJsonBodyFeature') && $this->shouldOverwriteDefaults() === false) {
            return array_merge($this->allData(), $this->connector->allData());
        }

        return $this->allData();
    }
}
