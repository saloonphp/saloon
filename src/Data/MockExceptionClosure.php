<?php

namespace Sammyjo20\Saloon\Data;

use Psr\Http\Message\RequestInterface;
use Throwable;

class MockExceptionClosure
{
    /**
     * @param mixed $closure
     */
    public function __construct(
        public mixed $closure
    )
    {
        //
    }

    /**
     * @param RequestInterface $request
     * @return Throwable
     */
    public function call(RequestInterface $request): Throwable
    {
        return call_user_func($this->closure, $request);
    }
}
