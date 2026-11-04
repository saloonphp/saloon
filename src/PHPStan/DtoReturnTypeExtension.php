<?php

declare(strict_types=1);

namespace Saloon\PHPStan;

use PHPStan\Type\Type;
use Saloon\Http\Response;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;

/**
 * Types dto() and dtoOrFail() as the return type of the request's createDtoFromResponse().
 */
final class DtoReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(
        private CreateDtoMethodResolver $resolver,
    ) {
    }

    public function getClass(): string
    {
        return Response::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $this->resolver->isDtoMethod($methodReflection);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $dtoType = $this->resolver->resolve($methodCall, $scope)?->getOnlyVariant()->getReturnType();

        return $dtoType instanceof MixedType ? null : $dtoType;
    }
}
