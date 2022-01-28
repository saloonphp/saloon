<?php

namespace Sammyjo20\Saloon\Tests\Resources\Responses;

use Sammyjo20\Saloon\Http\SaloonResponse;

class UserResponse extends SaloonResponse
{

	/**
	 * @return \Sammyjo20\Saloon\Tests\Resources\Responses\UserCustomResponse
	 */
	public function customCastMethod(): UserCustomResponse
	{
		return new UserCustomResponse($this->json("foo"));
	}

	/**
	 * @return string|null
	 */
	public function foo(): ?string
	{
		return $this->json("foo");
	}

}