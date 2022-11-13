<?php declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Exceptions\UnableToCastToStringException;

class MultipartBodyRepository extends ArrayBodyRepository
{
    /**
     * Convert to string
     *
     * @return string
     * @throws UnableToCastToStringException
     */
    public function __toString(): string
    {
        throw new UnableToCastToStringException('Casting the MultipartBodyRepository as a string is currently not supported.');
    }
}
