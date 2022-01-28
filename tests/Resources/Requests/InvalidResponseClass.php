<?php

namespace Sammyjo20\Saloon\Tests\Resources\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Resources\Responses\UserResponseNoExtendSaloonResponse;

class InvalidResponseClass extends SaloonRequest
{
	/**
	 * Define the method that the request will use.
	 *
	 * @var string|null
	 */
	protected ?string $method = Saloon::GET;

	protected ?string $response = UserResponseNoExtendSaloonResponse::class;

	/**
	 * The connector.
	 *
	 * @var string|null
	 */
	protected ?string $connector = TestConnector::class;

	/**
	 * Define the endpoint for the request.
	 *
	 * @return string
	 */
	public function defineEndpoint(): string
	{
		return '/user';
	}
}