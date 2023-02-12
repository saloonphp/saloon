<?php

namespace Saloon\Tests\Fixtures\Paginators;

use ReturnTypeWillChange;
use Saloon\Contracts\Request;
use Saloon\Http\Paginators\Paginator;

class MinimalPaginator extends Paginator
{
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
     * Called by this base RequestPaginator, when it's rewinding.
     *
     * @return void
     */
    protected function reset(): void
    {
        $this->currentPage = 1;
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
    }

    /**
     * Check if the paginator has any more pages
     *
     * @return bool
     */
    protected function isFinished(): bool
    {
        return is_null($this->currentResponse->json('next_page_url'));
    }

    /**
     * Get the current key of the iterator
     *
     * @return string|int
     */
    #[ReturnTypeWillChange]
    public function key(): string|int
    {
        return $this->currentPage;
    }
}
