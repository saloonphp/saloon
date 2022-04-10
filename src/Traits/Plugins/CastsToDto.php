<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;

trait CastsToDto
{
    /**
     * Boot the castsToDto plugin. This will create a response interceptor that will populate the DTO
     * property on the response.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function bootCastsToDto(SaloonRequest $request): void
    {
        $this->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
            if ($response->successful()) {
                $response->setDto($this->castToDto($response));
            }

            return $response;
        });
    }

    /**
     * Define how Saloon should cast to your DTO.
     *
     * @param SaloonResponse $response
     * @return mixed
     */
    abstract protected function castToDto(SaloonResponse $response): mixed;
}
