<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

/**
 * @method int limit()
 */
trait HasOffsetPagination
{
    use HasPagination;

    /**
     * @var string
     */
    protected string $offsetName = 'offset';

    /**
     * @var int
     */
    protected int $currentOffset = 0;

    /**
     * @param string $offsetName
     *
     * @return $this
     */
    public function usingOffsetName(string $offsetName): static
    {
        $this->offsetName = $offsetName;

        return $this;
    }

    /**
     * @return string
     */
    public function offsetName(): string
    {
        return $this->offsetName;
    }

    /**
     * @return int
     */
    public function currentOffset(): int
    {
        return $this->currentOffset;
    }
}
