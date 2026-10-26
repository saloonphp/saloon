<?php

declare(strict_types=1);

namespace Saloon\Tests\PHPStan;

use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DtoReturnTypeExtensionTest extends TypeInferenceTestCase
{
    /**
     * @return array<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/phpstan-test.neon'];
    }

    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__ . '/Fixtures/dto-return-types.php');
    }

    #[DataProvider('dataFileAsserts')]
    public function testReturnTypes(string $assertType, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }
}
