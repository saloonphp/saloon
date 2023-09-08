<?php

declare(strict_types=1);

namespace Saloon;

use Saloon\Contracts\Sender;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Helpers\MiddlewarePipeline;

final class Config
{
    /**
     * Default Sender
     *
     * @var class-string<\Saloon\Contracts\Sender>
     */
    public static string $defaultSender = GuzzleSender::class;

    /**
     * Default TLS Method (v1.2)
     */
    public static int $defaultTlsMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

    /**
     * Default timeout (in seconds) for establishing a connection.
     */
    public static int $defaultConnectionTimeout = 10;

    /**
     * Default timeout (in seconds) for making requests
     */
    public static int $defaultRequestTimeout = 30;

    /**
     * Resolve the sender with a callback
     *
     * @var callable|null
     */
    private static mixed $senderResolver = null;

    /**
     * Global Middleware Pipeline
     */
    private static ?MiddlewarePipeline $globalMiddlewarePipeline = null;

    /**
     * Write a custom sender resolver
     */
    public static function setSenderResolver(?callable $senderResolver): void
    {
        self::$senderResolver = $senderResolver;
    }

    /**
     * Create a new default sender
     */
    public static function getDefaultSender(): Sender
    {
        $senderResolver = self::$senderResolver;

        return is_callable($senderResolver) ? $senderResolver() : new self::$defaultSender;
    }

    /**
     * Update global middleware
     */
    public static function globalMiddleware(): MiddlewarePipeline
    {
        return self::$globalMiddlewarePipeline ??= new MiddlewarePipeline;
    }

    /**
     * Reset global middleware
     */
    public static function clearGlobalMiddleware(): void
    {
        self::$globalMiddlewarePipeline = null;
    }
}
