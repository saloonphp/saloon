<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Spatie\Ray\Ray;
use Spatie\Ray\Client;
use Saloon\Debugging\DebugData;

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
     * Check if the debugging driver can be used
     *
     * @return bool
     */
    public function hasDependencies(): bool
    {
        return class_exists(Ray::class);
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     */
    public function send(DebugData $data): void
    {
        Ray::create(self::$rayClient, self::$rayUuid)->send($this->formatData($data))->label('Saloon Debugger');
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
