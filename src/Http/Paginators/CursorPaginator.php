<?php

namespace Saloon\Http\Paginators;

use ReturnTypeWillChange;
use Saloon\Contracts\Request;

class CursorPaginator extends Paginator
{
    /**
     * Move the iterator to the next item
     *
     * @return void
     */
    public function next(): void
    {
        // TODO: Implement next() method.
    }

    /**
     * Called by this base paginator, when it's rewinding.
     *
     * @return void
     */
    protected function reset(): void
    {
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
        // TODO: Implement applyPagination() method.
    }

    /**
     * Check if the paginator has any more pages
     *
     * @return bool
     */
    protected function isFinished(): bool
    {
        // TODO: Implement isFinished() method.
    }

    /**
     * Get the current key of the iterator
     *
     * @return string|int
     */
    #[ReturnTypeWillChange]
    public function key(): string|int
    {
        // TODO: Implement key() method.
    }
}
