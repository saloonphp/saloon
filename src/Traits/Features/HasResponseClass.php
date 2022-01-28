<?php

namespace Sammyjo20\Saloon\Traits\Features;

use Sammyjo20\Saloon\Http\SaloonResponse;

trait HasResponseClass
{

	protected ?string $response = null;

	public function getResponseClass() : string
	{
		return $this->response ?? SaloonResponse::class;
	}

}