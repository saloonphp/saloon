<?php

declare(strict_types=1);

namespace Saloon\Enums;

enum Method: string
{
    case GET = 'GET';
    case HEAD = 'HEAD';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case OPTIONS = 'OPTIONS';
    case CONNECT = 'CONNECT';
    case TRACE = 'TRACE';
}
