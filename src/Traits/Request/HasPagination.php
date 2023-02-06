<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

trait HasPagination
{
    /**
     * @var string
     */
    protected string $limitName = 'limit';

    protected readonly ?int $limit;

    /**
     * @param string $limitName
     *
     * @return $this
     */
    public function usingLimitName(string $limitName): static
    {
        $this->limitName = $limitName;

        return $this;
    }

    /**
     * @return string
     */
    public function limitName(): string
    {
        return $this->limitName;
    }

    /**
     * @return int|null
     */
    public function limit(): ?int
    {
        return $this->limit;
    }
}
