<?php

namespace Sammyjo20\Saloon\Helpers;

use ReflectionClass;

class ReflectionHelper
{
    /**
     * Check if a class is a subclass of another.
     *
     * @param string $class
     * @param string $subclass
     * @return bool
     * @throws \ReflectionException
     */
    public static function isSubclassOf(string $class, string $subclass): bool
    {
        if ($class === $subclass) {
            return true;
        }

        return (new ReflectionClass($class))->isSubclassOf($subclass);
    }
}
