<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Responses;

use Saloon\Http\Responses\PsrResponse;

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
