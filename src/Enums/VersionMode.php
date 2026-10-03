<?php

declare(strict_types=1);

namespace Saloon\Enums;

enum VersionMode
{
    case Header;        // e.g., api-version: 2026-10
    case QueryParam;    // e.g., ?api-version=2026-10
    case Url;           // e.g., https://{version}.api.com or https://api.com/{version}
}
