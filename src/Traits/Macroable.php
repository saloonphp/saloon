<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use BadMethodCallException;

/**
 * Many thanks to Spatie for building this excellent trait.
 *
 * @see https://github.com/spatie/macroable
 */
trait Macroable
{
    /**
     * Macros stored
     *
     * @var array<object|callable>
     */
    protected static array $macros = [];

    /**
     * Create a macro
     */
    public static function macro(string $name, object|callable $macro): void
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Add a mixin
     *
     * @param object|class-string $mixin
     */
    public static function mixin(object|string $mixin): void
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            $method->setAccessible(true);

            static::macro($method->name, $method->invoke($mixin));
        }
    }

    /**
     * Check if we have a macro
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Handle a static call
     *
     * @param array<string, mixed> $parameters
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            return call_user_func_array(Closure::bind($macro, null, static::class), $parameters);
        }

        return call_user_func_array($macro, $parameters);
    }

    /**
     * Handle a method call
     *
     * @param array<string, mixed> $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            return call_user_func_array($macro->bindTo($this, static::class), $parameters);
        }

        return call_user_func_array($macro, $parameters);
    }
}
