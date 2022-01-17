<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Constants\SaloonHandlers;

trait CachesRequests
{
    public function bootCachesRequestsFeature(): void
    {
        $this->addHandler(SaloonHandlers::CACHE, function () {
            // Return middleware...
        });
    }
}
