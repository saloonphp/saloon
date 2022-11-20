<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;

interface Dispatcher
{
    /**
     * Execute the action
     *
     * @return \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface
     */
    public function execute(): Response|PromiseInterface;
}
