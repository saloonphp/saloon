<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use ReturnTypeWillChange;
use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;

class CursorPaginator extends Paginator
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
     * The current cursor that was provided
     *
     * @var string|int|null
     */
    protected string|int|null $currentCursor = null;

    /**
     * The JSON key for the next page URL that contains the cursor
     *
     * @var string
     */
    protected string $nextPageKeyName = 'next_page_url';

    /**
     * The key/query parameter that contains the cursor
     *
     * @var string
     */
    protected string $cursorKeyName = 'cursor';

    /**
     * Constructor
     *
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int $limit
     * @param int $page
     */
    public function __construct(
        Connector $connector,
        Request   $originalRequest,
        int       $limit,
        int       $page = 1,
    ) {
        parent::__construct($connector, $originalRequest, $limit);

        $this->currentPage = $this->originalPage = $page;
    }

    /**
     * Move the iterator to the next item
     *
     * @return void
     */
    public function next(): void
    {
        $this->currentPage++;
        $this->currentCursor = $this->getCursor();
    }

    /**
     * Called by this base paginator, when it's rewinding.
     *
     * @return void
     */
    protected function reset(): void
    {
        $this->currentPage = $this->originalPage;
        $this->currentCursor = null;
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
        if (! is_null($this->limit())) {
            $request->query()->add($this->getLimitKeyName(), $this->limit());
        }

        $request->query()->add($this->getCursorKeyName(), $this->currentCursor);
    }

    /**
     * Check if the paginator has any more pages
     *
     * @return bool
     * @throws \Saloon\Exceptions\PaginatorException
     */
    protected function isFinished(): bool
    {
        if ($this->isAsync()) {
            return $this->currentPage > $this->totalPages();
        }

        return is_null($this->currentResponse->json($this->getNextPageKeyName()));
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

    /**
     * Get the cursor
     *
     * @return string|int|null
     */
    protected function getCursor(): string|int|null
    {
        $rawQuery = parse_url($this->currentResponse->json($this->getNextPageKeyName()) ?? '', PHP_URL_QUERY);

        if (empty($rawQuery)) {
            return null;
        }

        parse_str($rawQuery, $query);

        $cursorValue = $query[$this->getCursorKeyName()] ?? null;

        return is_array($cursorValue) ? $cursorValue[0] : $cursorValue;
    }

    /**
     * Set the JSON key for the next page URL that contains the cursor
     *
     * @param string $nextPageKeyName
     * @return $this
     */
    public function setNextPageKeyName(string $nextPageKeyName): static
    {
        $this->nextPageKeyName = $nextPageKeyName;

        return $this;
    }

    /**
     * Set the key/query parameter that contains the cursor
     *
     * @param string $cursorKeyName
     * @return $this
     */
    public function setCursorKeyName(string $cursorKeyName): static
    {
        $this->cursorKeyName = $cursorKeyName;

        return $this;
    }

    /**
     * Get the next page key
     *
     * @return string
     */
    public function getNextPageKeyName(): string
    {
        return $this->nextPageKeyName;
    }

    /**
     * Get the cursor key
     *
     * @return string
     */
    public function getCursorKeyName(): string
    {
        return $this->cursorKeyName;
    }
}
