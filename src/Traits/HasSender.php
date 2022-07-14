<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Http\Senders\GuzzleSender;
use Sammyjo20\Saloon\Interfaces\SenderInterface;

trait HasSender
{
    /**
     * The request sender.
     *
     * @var SenderInterface
     */
    protected SenderInterface $sender;

    /**
     * Manage the request sender.
     *
     * @return SenderInterface
     */
    public function sender(): SenderInterface
    {
        return $this->sender ??= $this->defaultSender();
    }

    /**
     * Define the default request sender.
     *
     * @return SenderInterface
     */
    protected function defaultSender(): SenderInterface
    {
        return new GuzzleSender;
    }
}
