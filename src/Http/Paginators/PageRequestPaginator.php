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

        return $this->currentResponse->json('total');
    }

    /**
     * @return iterable<int, array<string, mixed>>
     *
     * @TODO entry data type
     */
    public function entries(): iterable
    {
        return $this->currentResponse->json('data');
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

        return $this->lastPage();
    }

    /**
     * @return int
     */
    public function firstPage(): int
    {
        return 1;
    }

    /**
     * @return int|null
     */
    public function previousPage(): ?int
    {
        return $this->currentPage() > $this->firstPage() ? $this->currentPage() - 1 : null;
    }

    /**
     * @return int|null
     */
    public function nextPage(): ?int
    {
        return $this->currentPage() < $this->totalPages() ? $this->currentPage() + 1 : null;
    }

    /**
     * @return int
     */
    public function lastPage(): int
    {
        return $this->currentResponse->json('last_page');
    }

    /**
     * @param \Saloon\Contracts\Request $request
     *
     * @return void
     */
    protected function applyPaging(Request $request): void
    {
        if (! is_null($this->limit())) {
            $request->query()->add($this->limitName, $this->limit());
        }

        $request->query()->add($this->pageName, $this->currentPage());
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        if (! $this->shouldRewind()) {
            return;
        }

        parent::rewind();

        // Rewind to the original page, instead of strictly the first page.
        // Otherwise it could be an 'unexpected' behaviour, if we don't start over from where we started.
        $this->currentPage = $this->originalPage;
    }

    /**
     * @return int
     */
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

    /**
     * @return array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit_name: string,
     *     limit: int|null,
     *     rewinding_enabled: bool,
     *     page_name: string,
     *     original_page: int,
     *     current_page: int,
     * }
     *
     * @see \Saloon\Http\Paginators\PageRequestPaginator::__serialize()
     */
    public function jsonSerialize(): array
    {
        return $this->__serialize();
    }

    /**
     * @return array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit_name: string,
     *     limit: int|null,
     *     rewinding_enabled: bool,
     *     page_name: string,
     *     original_page: int,
     *     current_page: int,
     * }
     */
    public function __serialize(): array
    {
        return [
            ...parent::__serialize(),
            'page_name' => $this->pageName,
            'original_page' => $this->originalPage,
            'current_page' => $this->currentPage,
        ];
    }

    /**
     * @param array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit_name: string,
     *     limit: int|null,
     *     rewinding_enabled: bool,
     *     page_name: string,
     *     original_page: int,
     *     current_page: int,
     * } $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->pageName = $data['page_name'];
        $this->originalPage = $data['original_page'];
        $this->currentPage = $data['current_page'];
    }
}
