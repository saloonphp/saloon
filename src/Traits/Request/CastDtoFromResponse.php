<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Contracts\DataObjects\FromResponse;
use Saloon\Contracts\Response;
use Saloon\Exceptions\DataObjectException;

trait CastDtoFromResponse
{
    /**
     * Data Object To Cast To
     *
     * @var string
     */
    protected string $responseDataObject = '';

    /**
     * Cast the response to a DTO.
     *
     * @param Response $response
     * @return mixed
     * @throws \Saloon\Exceptions\DataObjectException
     */
    public function createDtoFromResponse(Response $response): mixed
    {
        $dataObject = $this->responseDataObject;

        if (empty($dataObject)) {
            return null;
        }

        if (! in_array(FromResponse::class, class_implements($dataObject), true)) {
            throw new DataObjectException(sprintf('When using the `responseDataObject` property the class must implement the %s interface.', FromResponse::class));
        }

        return $dataObject::fromResponse($response);
    }
}
