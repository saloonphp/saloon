<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Traits\Request\HasPagedPagination;

// TODO 1: Look into serialising the Connector and original Request,
//           to ensure that we can rebuild the paginator state without storing the entire multiverse.

class PageRequestPaginator extends RequestPaginator
{
    use HasPagedPagination;

    /**
     * @var int
     */
    protected readonly int $originalPage;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int|null $limit
     * @param int $page
     */
    public function __construct(
        Connector $connector,
        Request $originalRequest,
        ?int $limit = null,
        int $page = 1,
    ) {
        parent::__construct($connector, $originalRequest, $limit);

        $this->currentPage = $this->originalPage = $page;
    }

    /**
     * @return int
     */
    public function totalEntries(): int
    {
        // Make sure we have a response.
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return $this->currentResponse->json('json');
    }

    /**
     * @return int
     */
    public function totalPages(): int
    {
        // Make sure we have a response.
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return $this->currentResponse->json('last_page');
    }

    protected function reset(): void
    {
        // Rewind to the original page, instead of strictly the first page.
        // Otherwise it could be an 'unexpected' behaviour, if we don't start over from where we started.
        $this->currentPage = $this->originalPage;
    }

    /**
     * @param \Saloon\Contracts\Request $request
     *
     * @return void
     */
    protected function applyPagination(Request $request): void
    {
        if (! is_null($this->limit())) {
            $request->query()->add($this->limitName(), $this->limit());
        }

        $request->query()->add($this->pageName(), $this->currentPage());
    }

    /**
     * @return bool
     */
    protected function isFinished(): bool
    {
        // Because of how Iterators are iterated, we need to check
        //   if the current page is more than the total pages.
        // Checking if it's equal to total pages will fall 1 page short,
        //   as Iterators are first increased, then checked for validity.
        return $this->currentPage() > $this->totalPages();
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->currentPage();
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->currentPage++;
    }
}
