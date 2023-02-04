<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use JsonSerializable;

interface SerialisableRequestPaginator extends JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    function jsonSerialize(): array;

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array;

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function __unserialize(array $data): void;
}
