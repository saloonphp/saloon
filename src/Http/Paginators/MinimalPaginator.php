<?php

namespace Saloon\Http\Paginators;

use ReturnTypeWillChange;
use Saloon\Contracts\Request;

class MinimalPaginator extends Paginator
{
    /**
     * The current page of the iterator
     *
     * @var int
     */
    protected int $currentPage = 1;

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
     * Check if the paginator has any more pages
     *
     * @return bool
     */
    protected function isFinished(): bool
    {
        if ($this->async === true) {
            return $this->currentPage > ((int)ceil($this->totalEntries() / $this->limit));
        }

        return is_null($this->currentResponse->json('next_page_url'));
    }

    /**
     * Total entries this requested resource has on the current page, regardless of amount of queries done or that will be made.
     *
     * @return int
     */
    public function totalEntriesInResponse(): int
    {
        // Don't need
        // TODO: Implement totalEntriesInResponse() method.
    }

    /**
     * Total entries this requested resource has, regardless of amount of queries done or that will be made.
     *
     * @return int
     */
    public function totalEntries(): int
    {
        return $this->currentResponse->json('total');

        // Don't need
        // TODO: Implement totalEntries() method.
    }

    /**
     * Called by this base RequestPaginator, when it's rewinding.
     *
     * @return void
     */
    protected function reset(): void
    {
        $this->currentPage = 1;

        // TODO: Implement reset() method.
    }

    /**
     * Apply paging information, like setting the Request query parameter 'page', or 'offset', etc.
     *
     * @param \Saloon\Contracts\Request $request
     *
     * @return void
     */
    protected function applyPagination(Request $request): void
    {
        $request->query()->add('page', $this->currentPage);

        // TODO: Implement applyPagination() method.
    }

    /**
     * Get the current key of the iterator
     *
     * @return string|int
     */
    #[ReturnTypeWillChange] public function key(): string|int
    {
        return $this->currentPage;
    }

    /**
     * Total pages the requested resource has, given the payload, query parameters, etc, that are sent.
     *
     * @return int
     */
    public function totalPages(): int
    {
        // TODO: Implement totalPages() method.
    }
}
