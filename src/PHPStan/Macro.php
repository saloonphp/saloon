<?php

declare(strict_types=1);

namespace Saloon\PHPStan;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Reflection\ClassMemberReflection;

final class Macro implements MethodReflection
{
    /**
     * The is static.
     */
    private bool $isStatic = false;

    public function __construct(private ClassReflection $classReflection, private string $methodName, private ClosureType $closureType)
    {
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /**
     * Set the is static value.
     */
    public function setIsStatic(bool $isStatic): void
    {
        $this->isStatic = $isStatic;
    }

    public function getDocComment(): string|null
    {
        return null;
    }

    public function getName(): string
    {
        return $this->methodName;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    /** @inheritDoc */
    public function getVariants(): array
    {
        return [
            new FunctionVariant(TemplateTypeMap::createEmpty(), null, $this->closureType->getParameters(), $this->closureType->isVariadic(), $this->closureType->getReturnType()),
        ];
    }

    public function getDeprecatedDescription(): string|null
    {
        return null;
    }

    public function getThrowType(): null
    {
        return null;
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }
}
