<?php

declare(strict_types=1);

namespace Saloon\Enums;

enum Timeout: int
{
    case CONNECT = 10;
    case REQUEST = 30;
}
