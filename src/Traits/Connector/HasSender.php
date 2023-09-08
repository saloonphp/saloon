<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Config;
use Saloon\Contracts\Sender;

trait HasSender
{
    /**
     * Specify the default sender
     */
    protected string $defaultSender = '';

    /**
     * The request sender.
     */
    protected Sender $sender;

    /**
     * Manage the request sender.
     */
    public function sender(): Sender
    {
        return $this->sender ??= $this->defaultSender();
    }

    /**
     * Define the default request sender.
     */
    protected function defaultSender(): Sender
    {
        if (empty($this->defaultSender)) {
            return Config::getDefaultSender();
        }

        return new $this->defaultSender;
    }
}
