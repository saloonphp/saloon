<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

use Saloon\Contracts\Body\BodyRepository;

class InvalidBodyReturnTypeException extends PendingRequestException
{
    public function __construct(string $objectName)
    {
        parent::__construct(sprintf('The `body()` method on your %s must return an instance of %s.', $objectName, BodyRepository::class));
    }
}
