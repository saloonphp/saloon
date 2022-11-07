<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Helpers;

use Exception;

class Environment
{
    /**
     * Check if the environment detects Laravel.
     *
     * @return bool
     */
    public static function detectsLaravel(): bool
    {
        try {
            return function_exists('resolve') && resolve('saloon') instanceof \Sammyjo20\SaloonLaravel\Saloon;
        } catch (Exception $ex) {
            return false;
        }
    }
}
