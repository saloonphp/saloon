<?php declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

trait HasRequestProperties
{
    use HasHeaders;
    use HasQueryParameters;
    use HasConfig;
    use HasMiddleware;
}
