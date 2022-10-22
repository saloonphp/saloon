<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Data;

class FixtureData
{
    public static function fromFileContents(string $contents): static
    {
        $fileData = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        dd($fileData);
    }

    public function toFile(): string
    {
        // JSON encode the properties
    }
}
