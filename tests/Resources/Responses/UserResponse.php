<?php

namespace Sammyjo20\Saloon\Tests\Resources\Responses;

use Sammyjo20\Saloon\Http\SaloonResponse;

class UserResponse extends SaloonResponse
{

	/**
	 * @return \Sammyjo20\Saloon\Tests\Resources\Responses\UserCustomResponse
	 */
	public function customUserResponseMethod(): UserCustomResponse
	{
		return new UserCustomResponse($this->json("foo"));
	}

}