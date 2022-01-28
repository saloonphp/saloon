<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Tests\Resources\Requests\InvalidResponseClass;

class InvalidResponseConnector extends SaloonConnector
{

	protected ?string $response = InvalidResponseClass::class;

	/**
	 * Define the base url of the api.
	 *
	 * @return string
	 */
	public function defineBaseUrl(): string
	{
		return apiUrl();
	}
}
