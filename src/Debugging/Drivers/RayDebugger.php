<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Debugging\DebugData;
use Spatie\Ray\Client;
use Spatie\Ray\Ray;

class RayDebugger extends DebuggingDriver
{
    /**
     * Spatie Ray Client
     *
     * @var \Spatie\Ray\Client|null
     */
    private static ?Client $rayClient = null;

    /**
     * Spatie Ray UUID
     *
     * @var string|null
     */
    private static ?string $rayUuid = null;

    /**
     * Define the debugger name
     *
     * @return string
     */
    public function name(): string
    {
        return 'ray';
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     */
    public function send(DebugData $data): void
    {
        Ray::create(static::$rayClient, static::$rayUuid)->send($this->formatData($data))->label('Saloon Debugger');
    }

    /**
     * Set the Spatie Ray instance
     *
     * @param \Spatie\Ray\Client|null $rayClient
     * @param string|null $rayUuid
     * @return void
     */
    public static function setRay(?Client $rayClient = null, ?string $rayUuid = null): void
    {
        self::$rayClient = $rayClient;
        self::$rayUuid = $rayUuid;
    }
}
