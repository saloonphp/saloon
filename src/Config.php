<?php

declare(strict_types=1);

namespace Saloon;

use Saloon\Enums\PipeOrder;
use Saloon\Contracts\Sender;
use Saloon\Http\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Exceptions\StrayRequestException;

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
     * Whether stray requests should be prevented
     */
    private static bool $preventStrayRequests = false;

    /**
     * Custom sleep handler used instead of usleep()
     *
     * @var callable|null
     */
    private static mixed $sleepHandler = null;

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

    /**
     * Throw an exception if a request without a MockClient is made.
     */
    public static function preventStrayRequests(): void
    {
        self::$preventStrayRequests = true;

        self::globalMiddleware()->onRequest(static function (PendingRequest $pendingRequest) {
            if (self::$preventStrayRequests && ! $pendingRequest->hasMockClient()) {
                throw new StrayRequestException;
            }
        }, order: PipeOrder::LAST);
    }

    /**
     * Allow stray requests without a MockClient.
     */
    public static function allowStrayRequests(): void
    {
        self::$preventStrayRequests = false;
    }

    /**
     * Write a custom sleep handler
     *
     * The handler receives the number of microseconds to sleep for. Useful
     * for skipping or observing sleeps in tests. Pass null to restore the
     * default usleep() behaviour.
     */
    public static function sleepUsing(?callable $sleepHandler): void
    {
        self::$sleepHandler = $sleepHandler;
    }

    /**
     * Sleep for the given number of microseconds
     */
    public static function sleep(int $microseconds): void
    {
        $sleepHandler = self::$sleepHandler;

        is_callable($sleepHandler) ? $sleepHandler($microseconds) : usleep($microseconds);
    }
}
