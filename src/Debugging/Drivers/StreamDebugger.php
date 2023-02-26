<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use InvalidArgumentException;
use Saloon\Debugging\DebugData;

class StreamDebugger extends DebuggingDriver
{
    /**
     * Resource
     *
     * @var resource
     */
    protected mixed $resource;

    /**
     * Constructor
     *
     * @param resource|string $resource
     */
    public function __construct(mixed $resource)
    {
        if (is_string($resource)) {
            $resource = fopen($resource, 'wb');
        }

        if (! is_resource($resource)) {
            throw new InvalidArgumentException('Invalid value for argument `$resource`. The value must be a resource like fopen.');
        }

        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'file';
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     * @throws \JsonException
     */
    public function send(DebugData $data): void
    {
        $message = $data->wasNotSent() ? 'Saloon Request' : 'Saloon Response';
        $encoded = json_encode($this->formatData($data, true), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        fwrite($this->resource, sprintf('%s:%s%s %s', $message, PHP_EOL, $encoded, PHP_EOL));
    }
}
