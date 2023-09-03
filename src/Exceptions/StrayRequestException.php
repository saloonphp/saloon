<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

class StrayRequestException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('Attempted to make a real API request! Make sure to use a MockClient or Saloon::fake() if you are using Laravel.');
    }
}
