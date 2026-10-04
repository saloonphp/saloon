<?php

declare(strict_types=1);

namespace Saloon\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Rules\Exceptions\MissingCheckedExceptionInFunctionThrowsRule;

/**
 * @extends \PHPStan\Testing\RuleTestCase<\PHPStan\Rules\Exceptions\MissingCheckedExceptionInFunctionThrowsRule>
 */
final class DtoThrowTypeExtensionTest extends RuleTestCase
{
    /**
     * @return array<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/phpstan-throw-test.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(MissingCheckedExceptionInFunctionThrowsRule::class);
    }

    public function testThrowTypePropagation(): void
    {
        $namespace = 'Saloon\Tests\PHPStan\Fixtures';

        $this->analyse([__DIR__ . '/Fixtures/dto-throw-types.php'], [
            [
                "Function {$namespace}\dtoWithoutDeclaring() throws checked exception JsonException but it's missing from the PHPDoc @throws tag.",
                14,
            ],
            [
                "Function {$namespace}\dtoOrFailWithoutDeclaring() throws checked exception JsonException but it's missing from the PHPDoc @throws tag.",
                19,
            ],
        ]);
    }
}
