<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Contracts\Sender;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Exceptions\StrayRequestException;
use Saloon\Contracts\MiddlewarePipeline as MiddlewarePipelineContract;

final class Config
{
    /**
     * Default sender
     *
     * Used to reset the default sender
     */
    private const DEFAULT_SENDER = GuzzleSender::class;

    /**
     * Middleware Pipeline
     */
    private static ?MiddlewarePipelineContract $middlewarePipeline = null;

    /**
     * Default Sender
     *
     * @var class-string<\Saloon\Contracts\Sender>
     */
    private static string $defaultSender = GuzzleSender::class;

    /**
     * Sender resolver
     *
     * @var callable|null
     */
    private static mixed $senderResolver = null;

    /**
     * TLS Method
     */
    private static int $tlsMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

    /**
     * Update global middleware
     */
    public static function middleware(): MiddlewarePipelineContract
    {
        return self::$middlewarePipeline ??= new MiddlewarePipeline;
    }

    /**
     * Write a custom sender resolver
     */
    public static function resolveSenderWith(?callable $senderResolver): void
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
     * Set the default sender
     *
     * @param class-string<\Saloon\Contracts\Sender> $senderClass
     */
    public static function setDefaultSender(string $senderClass): void
    {
        self::$defaultSender = $senderClass;
    }

    /**
     * Get the TLS method
     */
    public static function getTLSMethod(): int
    {
        return self::$tlsMethod;
    }

    /**
     * Set the TLS method
     */
    public static function setTLSMethod(int $tlsMethod): void
    {
        self::$tlsMethod = $tlsMethod;
    }

    /**
     * Reset global middleware
     */
    public static function resetMiddleware(): void
    {
        self::$middlewarePipeline = null;
    }

    /**
     * Reset the default sender
     */
    public static function resetDefaultSender(): void
    {
        self::$defaultSender = self::DEFAULT_SENDER;
    }

    /**
     * Throw an exception if a request without a MockClient is made.
     *
     * @return void
     */
    public static function preventStrayRequests(): void
    {
        self::middleware()->onRequest(static function (PendingRequest $pendingRequest) {
            if (! $pendingRequest->hasMockClient()) {
                throw new StrayRequestException;
            }
        });
    }
}
