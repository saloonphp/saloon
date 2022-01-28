<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;
use Sammyjo20\Saloon\Http\SaloonResponse;
use \ReflectionClass;

trait HasResponseClass
{

	protected ?string $response = null;

	/**
	 * Get the response class
	 *
	 * @return string
	 * @throws \ReflectionException
	 * @throws SaloonInvalidResponseClassException
	 */
	public function getResponseClass() : string
	{
		$response = $this->response;

		if(!$response) {
			$response = method_exists($this, "getConnector") ? $this->getConnector()->getResponseClass() : SaloonResponse::class;
		}

		if(!class_exists($response)) {
			throw new SaloonInvalidResponseClassException;
		}

		$isValidResponse = $response == SaloonResponse::class
			|| (new ReflectionClass($response))->isSubclassOf(SaloonResponse::class);

		if(!$isValidResponse) {
			throw new SaloonInvalidResponseClassException;
		}

		return $response;
	}

}