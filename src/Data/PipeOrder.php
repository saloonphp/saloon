<?php

declare(strict_types=1);

namespace Saloon\Data;

use Saloon\Enums\Order;

class PipeOrder
{
    /**
     * Constructor
     */
    public function __construct(
        public readonly Order $type,
    )
    {
        //
    }

    /**
     * Run the middleware first
     */
    public static function first(): self
    {
        return new self(Order::FIRST);
    }

    /**
     * Run the middleware last
     */
    public static function last(): self
    {
        return new self(Order::LAST);
    }
}
