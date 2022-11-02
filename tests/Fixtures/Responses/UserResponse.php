<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Responses;

use Sammyjo20\Saloon\Http\Responses\PsrResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

class UserResponse extends PsrResponse
{
    /**
     * @return \Sammyjo20\Saloon\Tests\Fixtures\Responses\UserData
     * @throws \JsonException
     */
    public function customCastMethod(): UserData
    {
        return new UserData($this->json('foo'));
    }

    /**
     * @return string|null
     * @throws \JsonException
     */
    public function foo(): ?string
    {
        return $this->json('foo');
    }
}
