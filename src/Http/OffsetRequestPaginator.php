<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;

// TODO 1: Look into serialising the Connector and original Request,
//           to ensure that we can rebuild the paginator state without storing the entire multiverse.
// TODO 2: The 'offset' type paging has different name for the offset, like 'offset', and 'maxResults'/'max_results'.
//         We make it easy to name the offset property to make the OffsetRequestPaginator more versatile.

class OffsetRequestPaginator extends RequestPaginator
{
    /**
     * @var int
     */
    protected readonly int $originalOffset;

    /**
     * @var int
     */
    protected int $offset;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int|null $limit
     * @param int $page
     * @param callable(\Saloon\Contracts\Response): bool $hasNextPage
     */
    public function __construct(
        Connector $connector,
        Request $originalRequest,
        int $limit,
        int $originalOffset,
        callable $hasNextPage,
    ) {
        parent::__construct($connector, $originalRequest, $limit, $hasNextPage);

        $this->offset = $this->originalOffset = $originalOffset;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        parent::rewind();

        $this->offset = $this->originalOffset;
    }

    /**
     * @return \Saloon\Contracts\Response
     */
    public function current(): Response
    {
        $request = clone $this->originalRequest;
        $request->query()->add('limit', $this->limit);
        $request->query()->add('offset', $this->offset);

        return $this->lastResponse = $this->connector->send($request);
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->offset;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->offset += $this->limit;
    }

    /**
     * Go to the previous page.
     * Note that this method is *not* part of PHP's {@see \Iterator}, and will not work in loops, unless manually called.
     *
     * @return void
     */
    public function previous(): void
    {
        $this->offset -= $this->limit;
    }

    /**
     * @return array{
     *     original_offset: int,
     *     offset: int,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'original_offset' => $this->originalOffset,
            'offset' => $this->offset,
        ];
    }

    /**
     * @return array{
     *     original_offset: int,
     *     offset: int,
     * }
     */
    public function __serialize(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * @param array{
     *     original_offset: int,
     *     offset: int,
     * } $data
     */
    public function __unserialize(array $data): void
    {
        $this->originalOffset = $data['original_offset'];
        $this->offset = $data['offset'];
    }
}
