<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use DateTimeImmutable;

final class DateHelper
{
    private static ?DateTimeImmutable $fixedTime = null;
    
    public static function now(): DateTimeImmutable
    {
        return self::$fixedTime ?? new DateTimeImmutable();
    }
    
    /**
     * This method is only for setting a fixed test time.
     */
    public static function setFixedTime(?DateTimeImmutable $time): void
    {
        self::$fixedTime = $time;
    }
    
    public static function useSystemTime(): void
    {
        self::$fixedTime = null;
    }
}
