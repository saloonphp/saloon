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
    
    public static function setFixedTime(?DateTimeImmutable $time): void
    {
        self::$fixedTime = $time;
    }
    
    public static function useSystemTime(): void
    {
        self::$fixedTime = null;
    }
}
