<?php

namespace App\Enums;

enum VersionMode
{
    case Header;        // e.g., api-version: 2026-10
    case QueryParam;    // e.g., ?api-version=2026-10
    case Subdomain;     // e.g., v2
    case UrlPath;       // e.g., /v2
}