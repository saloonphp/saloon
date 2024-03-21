<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Enums;

enum GenderEnum: string
{
    case FEMALE = 'female';
    case MALE = 'male';
    case NONBINARY = 'nonbinary';
}
