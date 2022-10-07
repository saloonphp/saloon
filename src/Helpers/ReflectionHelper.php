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

    public static function getTraitsRecursively($class): array
    {
        $traits = [];

        do {
            $traits = array_merge(class_uses($class, true), $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, true), $traits);
        }

        return array_unique($traits);
    }

    public static function classBaseName(string $class): string
    {
        $arr = explode('\\', $class);

        return end($arr);
    }
}
