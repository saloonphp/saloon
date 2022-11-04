<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Contracts\Sender;
use Sammyjo20\Saloon\Http\Senders\GuzzleSender;

class SenderHelper
{
    /**
     * Register the default sender
     *
     * @return Sender
     */
    public static function defaultSender(): Sender
    {
        $detectsLaravel = Environment::detectsLaravel() && function_exists('config');

        $defaultSender = $detectsLaravel === true ? config('saloon.default_sender') : GuzzleSender::class;

        return new $defaultSender;
    }
}
