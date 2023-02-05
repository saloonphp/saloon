<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;

// TODO 1: Look into serialising the Connector and original Request,
//           to ensure that we can rebuild the paginator state without storing the entire multiverse.
// TODO 2: Make it easier to extend the RequestPaginator. Preferably via callbacks, so we ideally don't even need separate classes.
// TODO 3: Because both page-based and offset-based pagination just bumps the 'paging' number,
//           would it make sense to do all pagination in _the_ RequestPaginator, but allow to specify the counter somehow?
//         Maybe a callback that receives a copy of the original Request, as well as the latest Response (which has the corresponding latest Request and PendingRequest),
//           and have that callback set the next 'page' on the new Request?

class PageRequestPaginator extends RequestPaginator
{
    /**
     * @var string 
     */
    protected string $pageName = 'page';

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
        ?int $limit,
        protected int $page = 1,
    ) {
        parent::__construct($connector, $originalRequest, $limit);

        $this->originalPage = $page;
    }

    /**
     * @param string $pageName
     *
     * @return $this
     */
    public function withPageName(string $pageName): static
    {
        $this->pageName = $pageName;

        return $this;
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
        // TODO: Or should we use something from the response?
        return 1;
    }

    /**
     * @return int|null
     */
    public function previousPage(): ?int
    {
        return $this->currentPage() > $this->firstPage()
            ? $this->currentPage() - 1
            : null;
    }

    /**
     * @return int
     */
    public function currentPage(): int
    {
        return $this->page;
    }

    /**
     * @return int|null
     */
    public function nextPage(): ?int
    {
        return $this->currentPage() < $this->totalPages()
            ? $this->currentPage() + 1
            : null;
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

        // TODO: Rewind completely, or rewind to originalPage?
        $this->page = 1;
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
        $this->page++;
    }

    /**
     * @return array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     rewinding_enabled: bool,
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
     *     limit: int|null,
     *     rewinding_enabled: bool,
     *     original_page: int,
     *     current_page: int,
     * }
     */
    public function __serialize(): array
    {
        return [
            ...parent::__serialize(),
            'original_page' => $this->originalPage,
            'current_page' => $this->page,
        ];
    }

    /**
     * @param array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     rewinding_enabled: bool,
     *     original_page: int,
     *     current_page: int,
     * } $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->originalPage = $data['original_page'];
        $this->page = $data['current_page'];
    }
}
