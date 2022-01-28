<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Tests\Resources\Responses\CustomResponse;

class CustomResponseConnector extends SaloonConnector
{

	protected ?string $response = CustomResponse::class;

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
