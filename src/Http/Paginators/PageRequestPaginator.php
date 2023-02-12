<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;
use ReturnTypeWillChange;

class PageRequestPaginator extends Paginator
{
    /**
     * The original page we started from
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
     * The query parameter name for the results
     *
     * @var string
     */
    protected string $resultsKeyName = 'data';

    /**
     * The query parameter name for the total
     *
     * @var string
     */
    protected string $totalKeyName = 'total';

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
        Request $originalRequest,
        int $perPage,
        int $page = 1,
    ) {
        parent::__construct($connector, $originalRequest, $perPage);

        $this->setLimitKeyName('per_page');

        $this->currentPage = $this->originalPage = $page;
    }

    /**
     * Get the total entries in the current response result set
     *
     * @return int
     */
    public function totalEntriesInResponse(): int
    {
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return count($this->currentResponse->json($this->resultsKeyName));
    }

    /**
     * Total entries for the whole resource
     *
     * @return int
     */
    public function totalEntries(): int
    {
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return $this->currentResponse->json($this->totalKeyName);
    }

    /**
     * Get the total pages in the result set
     *
     * @return int
     */
    public function totalPages(): int
    {
        return (int)ceil($this->totalEntries() / $this->limit);
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
            $request->query()->add($this->getLimitKeyName(), $this->limit());
        }

        $request->query()->add($this->pageKeyName, $this->currentPage());
    }

    /**
     * Check if the paginator has  finished
     *
     * @return bool
     */
    protected function hasMorePages(): bool
    {
        // Because of how iterators are iterated, we need to check if the current page
        // is more than the total pages. Checking if it's equal to total pages will
        // fall 1 page short, as Iterators are first increased, then checked for
        // validity.

        return $this->currentPage() > $this->totalPages();
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
     * Set the results key name
     *
     * @param string $resultsKeyName
     * @return PageRequestPaginator
     */
    public function setResultsKeyName(string $resultsKeyName): static
    {
        $this->resultsKeyName = $resultsKeyName;

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
}
