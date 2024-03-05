<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

use BackedEnum;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use Saloon\Helpers\StringHelpers;
use Saloon\Http\Request;

/** @mixin Request */
trait HasEndpointPlaceholders
{
    /**
     * Whenever you use this trait, you need to define the $endpoint property, e.g.:
     * protected string $endpoint = '/foo/{id}';
     */
    protected function getEndpointAttribute(): string
    {
        return $this->endpoint ?? throw new LogicException(
            'Your request class is missing an endpoint. Please add a property like [protected string $endpoint = \'/foo/{id}\'].'
        );
    }

    /**
     * Resolve endpoint with replacements.
     */
    public function resolveEndpoint(): string
    {
        return rtrim(StringHelpers::replacePlaceholders(
            $this->getEndpointAttribute(),
            $this->extractEndpointReplacements()
        ), '/');
    }

    /**
     * Using reflection to extract replacements from the constructor parameters (int|string|enum types only).
     */
    protected function extractEndpointReplacements(): array
    {
        $replacements = [];
        $params = (new ReflectionClass($this))->getConstructor()?->getParameters() ?? [];

        foreach ($params as $param) {
            $type = $param->getType();
            $name = $param->getName();

            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
            } elseif ($type instanceof ReflectionUnionType) {
                // Cannot use $type->getName() as it's not a named type,
                // and looping over $type->getTypes() does not help here.
                $typeName = is_object($this->$name)
                    ? get_class($this->$name)
                    : get_debug_type($this->$name);
            } else {
                // Ignore parameters without a type
                continue;
            }

            if (enum_exists($typeName)) {
                // WARNING: $this->$name could be instanceof BackedEnum but be null on optional param
                $replacements[$name] = $this->$name instanceof BackedEnum
                    ? $this->$name?->value // Use the enum value if it's a BackedEnum
                    : $this->$name?->name; // Use the enum name if it's a Basic enum
            } elseif ($typeName === 'bool') {
                // Replace boolean parameters with their name if true, or an empty string if false
                $replacements[$name] = $this->$name ? $name : '';
            } elseif (in_array($typeName, ['int', 'string'])) {
                // Replace int|string parameters with their value
                $replacements[$name] = $this->$name;
            }
        }

        return $replacements;
    }
}
