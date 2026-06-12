<?php

declare(strict_types=1);

namespace Saloon\PHPStan;

use Closure;
use function explode;
use function in_array;
use function is_array;
use function get_class;
use function is_string;
use function array_keys;
use ReflectionException;
use function is_callable;
use function str_contains;
use Saloon\Traits\Macroable;
use function array_key_exists;
use PHPStan\Type\ClosureTypeFactory;
use PHPStan\ShouldNotHappenException;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\MissingMethodFromReflectionException;

class MacroMethodsClassReflectionExtension implements MethodsClassReflectionExtension
{
    /** @var array<string, MethodReflection> */
    private array $methods = [];

    /** @var array<string, array<string, bool>> */
    private array $traitCache = [];

    public function __construct(private ReflectionProvider $reflectionProvider, private ClosureTypeFactory $closureTypeFactory)
    {
    }

    /**
     * @throws ReflectionException
     * @throws ShouldNotHappenException
     * @throws MissingMethodFromReflectionException
     */
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        /** @var class-string[] $classNames */
        $classNames = [];
        $found = false;
        $macroTraitProperty = null;

        if ($this->hasIndirectTraitUse($classReflection, Macroable::class)) {
            $classNames = [$classReflection->getName()];
            $macroTraitProperty = 'macros';
        }

        if ($classNames !== [] && $macroTraitProperty) {
            foreach ($classNames as $className) {
                $macroClassReflection = $this->reflectionProvider->getClass($className);

                if (! $macroClassReflection->getNativeReflection()->hasProperty($macroTraitProperty)) {
                    continue;
                }

                $refProperty = $macroClassReflection->getNativeReflection()->getProperty($macroTraitProperty);

                $found = array_key_exists($methodName, $refProperty->getValue());

                if (! $found) {
                    continue;
                }

                $macroDefinition = $refProperty->getValue()[$methodName];

                if (is_string($macroDefinition)) {
                    if (str_contains($macroDefinition, '::')) {
                        $macroDefinition = explode('::', $macroDefinition, 2);
                        $macroClassName = $macroDefinition[0];
                        if (! $this->reflectionProvider->hasClass($macroClassName) || ! $this->reflectionProvider->getClass($macroClassName)->hasNativeMethod($macroDefinition[1])) {
                            throw new ShouldNotHappenException('Class ' . $macroClassName . ' does not exist');
                        }

                        $methodReflection = $this->reflectionProvider->getClass($macroClassName)->getNativeMethod($macroDefinition[1]);
                    } elseif (is_callable($macroDefinition)) {
                        $methodReflection = new Macro(
                            $macroClassReflection,
                            $methodName,
                            $this->closureTypeFactory->fromClosureObject(Closure::fromCallable($macroDefinition)),
                        );
                    } else {
                        throw new ShouldNotHappenException('Function ' . $macroDefinition . ' does not exist');
                    }
                } elseif (is_array($macroDefinition)) {
                    if (is_string($macroDefinition[0])) {
                        $macroClassName = $macroDefinition[0];
                    } else {
                        $macroClassName = get_class($macroDefinition[0]);
                    }

                    if ($macroClassName === false || ! $this->reflectionProvider->hasClass($macroClassName) || ! $this->reflectionProvider->getClass($macroClassName)->hasNativeMethod($macroDefinition[1])) {
                        throw new ShouldNotHappenException('Class ' . $macroClassName . ' does not exist');
                    }

                    $methodReflection = $this->reflectionProvider->getClass($macroClassName)->getNativeMethod($macroDefinition[1]);
                } else {
                    $methodReflection = new Macro(
                        $macroClassReflection,
                        $methodName,
                        $this->closureTypeFactory->fromClosureObject($macroDefinition),
                    );

                    $methodReflection->setIsStatic(true);
                }

                $this->methods[$classReflection->getName() . '-' . $methodName] = $methodReflection;

                break;
            }
        }

        return $found;
    }

    public function getMethod(
        ClassReflection $classReflection,
        string $methodName,
    ): MethodReflection {
        return $this->methods[$classReflection->getName() . '-' . $methodName];
    }

    private function hasIndirectTraitUse(ClassReflection $class, string $traitName): bool
    {
        $className = $class->getName();

        if (array_key_exists($className, $this->traitCache) && array_key_exists($traitName, $this->traitCache[$className])) {
            return $this->traitCache[$className][$traitName];
        }

        $this->traitCache[$className][$traitName] = in_array($traitName, array_keys($class->getTraits(true)), true);

        return $this->traitCache[$className][$traitName];
    }
}
