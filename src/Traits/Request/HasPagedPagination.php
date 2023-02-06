<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

trait HasPagedPagination
{
    use HasPagination;

    /**
     * @var string
     */
    protected string $pageName = 'page';

    /**
     * @var int
     */
    protected int $currentPage = 1;

    /**
     * @param string $pageName
     *
     * @return $this
     */
    public function usingPageName(string $pageName): static
    {
        $this->pageName = $pageName;

        return $this;
    }

    /**
     * @return string
     */
    public function pageName(): string
    {
        return $this->pageName;
    }

    /**
     * @return int
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }
}
