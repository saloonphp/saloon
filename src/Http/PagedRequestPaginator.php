<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;

// TODO 1: Look into serialising the Connector and original Request,
//           to ensure that we can rebuild the paginator state without storing the entire multiverse.

class PagedRequestPaginator extends RequestPaginator
{
    /**
     * @var int
     */
    protected readonly int $originalPage;

    /**
     * @var int
     */
    protected int $page;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int|null $limit
     * @param int $originalPage
     * @param callable(\Saloon\Contracts\Response): bool $hasNextPage
     */
    public function __construct(
        Connector $connector,
        Request $originalRequest,
        int|null $limit,
        int $originalPage,
        callable $hasNextPage,
    ) {
        parent::__construct($connector, $originalRequest, $limit, $hasNextPage);

        $this->page = $this->originalPage = $originalPage;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        parent::rewind();

        $this->page = $this->originalPage;
    }

    /**
     * @return \Saloon\Contracts\Response
     */
    public function current(): Response
    {
        $request = clone $this->originalRequest;
        $request->query()->add('page', $this->page);

        return $this->lastResponse = $this->connector->send($request);
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->page;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->page++;
    }

    /**
     * Go to the previous page.
     * Note that this method is *not* part of PHP's {@see \Iterator}, and will not work in loops, unless manually called.
     *
     * @return void
     */
    public function previous(): void
    {
        $this->page--;
    }

    /**
     * @return array{
     *     original_page: int,
     *     page: int,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'original_page' => $this->originalPage,
            'page' => $this->page,
        ];
    }

    /**
     * @return array{
     *     original_page: int,
     *     page: int,
     * }
     */
    public function __serialize(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * @param array{
     *     original_page: int,
     *     page: int,
     * } $data
     */
    public function __unserialize(array $data): void
    {
        $this->originalPage = $data['original_page'];
        $this->page = $data['page'];
    }
}
