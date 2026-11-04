<?php

declare(strict_types=1);

namespace Saloon\PHPStan;

use Saloon\Http\Response;
use PHPStan\Analyser\Scope;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ExtendedMethodReflection;

final class CreateDtoMethodResolver
{
    public function isDtoMethod(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['dto', 'dtoOrFail'], true);
    }

    /**
     * Resolve createDtoFromResponse() on the request that produced the response.
     */
    public function resolve(MethodCall $methodCall, Scope $scope): ?ExtendedMethodReflection
    {
        $requestType = $scope->getType($methodCall->var)->getTemplateType(Response::class, 'TRequest');

        if (! $requestType->hasMethod('createDtoFromResponse')->yes()) {
            return null;
        }

        return $requestType->getMethod('createDtoFromResponse', $scope);
    }
}
