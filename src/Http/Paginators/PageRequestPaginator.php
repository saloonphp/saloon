<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use ReturnTypeWillChange;
use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;

class PageRequestPaginator extends Paginator
{
    /**
     * The original page the paginator started from
     *
     * @var int
     */
    protected readonly int $originalPage;

    /**
     * The current page of the iterator
     *
     * @var int
     */
    protected int $currentPage = 1;

    /**
     * The query parameter name for page
     *
     * @var string
     */
    protected string $pageKeyName = 'page';

    /**
     * The JSON key name for the total
     *
     * @var string
     */
    protected string $totalKeyName = 'total';

    /**
     * The JSON key name for the next page URL
     *
     * @var string
     */
    protected string $nextPageKeyName = 'next_page_url';

    /**
     * Constructor
     *
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int $perPage
     * @param int $page
     */
    public function __construct(
        Connector $connector,
        Request   $originalRequest,
        int       $perPage,
        int       $page = 1,
    ) {
        parent::__construct($connector, $originalRequest, $perPage);

        $this->currentPage = $this->originalPage = $page;
    }

    /**
     * Reset the iterator
     *
     * @return void
     */
    protected function reset(): void
    {
        // We'll rewind to the original page instead strictly the first page. This is so
        // we start from when the user requested us to start from, not actually the beginning.

        $this->currentPage = $this->originalPage;
    }

    /**
     * Apply the pagination to the current request instance
     *
     * @param \Saloon\Contracts\Request $request
     * @return void
     */
    protected function applyPagination(Request $request): void
    {
        if (! is_null($this->limit())) {
            $request->query()->add($this->limitKeyName, $this->limit());
        }

        $request->query()->add($this->pageKeyName, $this->currentPage());
    }

    /**
     * Check if the paginator has finished
     *
     * @return bool
     * @throws \Saloon\Exceptions\PaginatorException
     */
    protected function isFinished(): bool
    {
        if ($this->isAsync()) {
            return $this->currentPage() > $this->totalPages();
        }

        return is_null($this->currentResponse->json($this->nextPageKeyName));
    }

    /**
     * Get the current key of the iterator
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function key(): int
    {
        return $this->currentPage();
    }

    /**
     * Move the iterator to the next item
     *
     * @return void
     */
    public function next(): void
    {
        $this->currentPage++;
    }

    /**
     * Get the current page
     *
     * @return int
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Set the current page
     *
     * @param int $currentPage
     * @return PageRequestPaginator
     */
    public function setCurrentPage(int $currentPage): static
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * Set the page key name
     *
     * @param string $pageName
     * @return $this
     */
    public function setPageKeyName(string $pageName): static
    {
        $this->pageKeyName = $pageName;

        return $this;
    }

    /**
     * Set the total key name
     *
     * @param string $totalKeyName
     * @return PageRequestPaginator
     */
    public function setTotalKeyName(string $totalKeyName): static
    {
        $this->totalKeyName = $totalKeyName;

        return $this;
    }

    /**
     * Set the next page key name
     *
     * @param string $nextPageKeyName
     * @return PageRequestPaginator
     */
    public function setNextPageKeyName(string $nextPageKeyName): static
    {
        $this->nextPageKeyName = $nextPageKeyName;

        return $this;
    }
}
