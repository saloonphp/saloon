<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Interfaces\Data\HasJsonBody;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;

class UserRequest extends SaloonRequest implements HasJsonBody
{
    /**
     * Define the method that the request will use.
     *
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     *
     * @var string
     */
    protected string $connector = TestConnector::class;

    /**
     * @param int|null $userId
     * @param int|null $groupId
     */
    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        $this->middleware()
            ->addRequestPipe(function (PendingSaloonRequest $request) {
                $request->headers()->put('X-Name', 'Sam');
            });
    }

    /**
     * @return string
     */
    protected function defineEndpoint(): string
    {
        return '/user';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultData(): mixed
    {
        return [
            'foo' => 'bar',
        ];
    }
}
