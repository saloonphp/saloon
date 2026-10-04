<?php

declare(strict_types=1);

namespace Saloon\PHPStan;

use Throwable;
use PHPStan\Type\Type;
use Saloon\Http\Response;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodThrowTypeExtension;

/**
 * Adds the exceptions of the request's createDtoFromResponse() to dto() and dtoOrFail().
 */
final class DtoThrowTypeExtension implements DynamicMethodThrowTypeExtension
{
    public function __construct(
        private CreateDtoMethodResolver $resolver,
        private bool $implicitThrows,
    ) {
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getDeclaringClass()->is(Response::class)
            && $this->resolver->isDtoMethod($methodReflection);
    }

    public function getThrowTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $createDtoThrowType = $this->resolver->resolve($methodCall, $scope)?->getThrowType();

        if ($createDtoThrowType === null) {
            return $methodReflection->getThrowType() ?? $this->implicitThrowType();
        }

        return TypeCombinator::union($createDtoThrowType, ...array_filter([$methodReflection->getThrowType()]));
    }

    /**
     * PHPStan reads a null throw type as "throws nothing", so an undeclared
     * createDtoFromResponse() has to fall back to what PHPStan infers on its own.
     */
    private function implicitThrowType(): ?Type
    {
        return $this->implicitThrows ? new ObjectType(Throwable::class) : null;
    }
}
