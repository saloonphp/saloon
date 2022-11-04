<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Contracts\Sender;
use Sammyjo20\Saloon\Helpers\Environment;
use Sammyjo20\Saloon\Helpers\SenderHelper;
use Sammyjo20\Saloon\Http\Senders\GuzzleSender;

trait HasSender
{
    /**
     * The request sender.
     *
     * @var Sender
     */
    protected Sender $sender;

    /**
     * Manage the request sender.
     *
     * @return Sender
     */
    public function sender(): Sender
    {
        return $this->sender ??= $this->defaultSender();
    }

    /**
     * Define the default request sender.
     *
     * @return Sender
     */
    protected function defaultSender(): Sender
    {
        return SenderHelper::defaultSender();
    }
}
