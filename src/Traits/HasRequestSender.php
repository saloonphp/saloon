<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Http\Senders\GuzzleSender;
use Sammyjo20\Saloon\Interfaces\RequestSenderInterface;

trait HasRequestSender
{
    /**
     * The request sender.
     *
     * @var RequestSenderInterface
     */
    protected RequestSenderInterface $requestSender;

    /**
     * Manage the request sender.
     *
     * @return RequestSenderInterface
     */
    public function requestSender(): RequestSenderInterface
    {
        return $this->requestSender ??= $this->defaultRequestSender();
    }

    /**
     * Define the default request sender.
     *
     * @return RequestSenderInterface
     */
    protected function defaultRequestSender(): RequestSenderInterface
    {
        return new GuzzleSender;
    }
}
