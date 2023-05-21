<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Contracts\Sender;
use Saloon\Http\Senders\GuzzleSender;
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
     *
     * @var \Saloon\Contracts\MiddlewarePipeline|null
     */
    private static ?MiddlewarePipelineContract $middlewarePipeline = null;

    /**
     * Default Sender
     *
     * @var class-string<\Saloon\Contracts\Sender>
     */
    private static string $defaultSender = GuzzleSender::class;

    /**
     * Default User Agent
     *
     * @var string
     */
    private static string $userAgent = 'Saloon/3';

    /**
     * Sender resolver
     *
     * @var callable|null
     */
    private static mixed $senderResolver = null;

    /**
     * Update global middleware
     *
     * @return \Saloon\Contracts\MiddlewarePipeline
     */
    public static function middleware(): MiddlewarePipelineContract
    {
        return self::$middlewarePipeline ??= new MiddlewarePipeline;
    }

    /**
     * Write a custom sender resolver
     *
     * @param callable|null $senderResolver
     * @return void
     */
    public static function resolveSenderWith(?callable $senderResolver): void
    {
        self::$senderResolver = $senderResolver;
    }

    /**
     * Create a new default sender
     *
     * @return \Saloon\Contracts\Sender
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
     * @return void
     */
    public static function setDefaultSender(string $senderClass): void
    {
        self::$defaultSender = $senderClass;
    }

    /**
     * Reset global middleware
     *
     * @return void
     */
    public static function resetMiddleware(): void
    {
        self::$middlewarePipeline = null;
    }

    /**
     * Reset the default sender
     *
     * @return void
     */
    public static function resetDefaultSender(): void
    {
        self::$defaultSender = self::DEFAULT_SENDER;
    }

    /**
     * Get the User Agent
     *
     * @return string
     */
    public static function getUserAgent(): string
    {
        return self::$userAgent;
    }

    /**
     * Set the User Agent
     *
     * @param string $userAgent
     */
    public static function setUserAgent(string $userAgent): void
    {
        self::$userAgent = $userAgent;
    }
}
