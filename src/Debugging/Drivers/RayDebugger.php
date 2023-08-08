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
     */
    private static ?Client $rayClient = null;

    /**
     * Spatie Ray UUID
     */
    private static ?string $rayUuid = null;

    /**
     * Define the debugger name
     */
    public function name(): string
    {
        return 'ray';
    }

    /**
     * Check if the debugging driver can be used
     */
    public function hasDependencies(): bool
    {
        return class_exists(Ray::class);
    }

    
    public function send(DebugData $data): void
    {
        Ray::create(self::$rayClient, self::$rayUuid)->send($this->formatData($data))->label('Saloon Debugger');
    }

    /**
     * Set the Spatie Ray instance
     */
    public static function setRay(?Client $rayClient = null, ?string $rayUuid = null): void
    {
        self::$rayClient = $rayClient;
        self::$rayUuid = $rayUuid;
    }
}
