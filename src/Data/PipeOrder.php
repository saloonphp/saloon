<?php

namespace Saloon\Data;

use Saloon\Enums\Order;

class PipeOrder
{
    /**
     * Constructor
     *
     * @param \Saloon\Enums\Order $type
     */
    public function __construct(
        public readonly Order $type,
    )
    {
        //
    }

    /**
     * Run the middleware first
     *
     * @return self
     */
    public static function first(): self
    {
        return new self(Order::FIRST);
    }

    /**
     * Run the middleware last
     *
     * @return self
     */
    public static function last(): self
    {
        return new self(Order::LAST);
    }
}
