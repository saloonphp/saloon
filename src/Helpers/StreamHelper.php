<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Contracts\MultipartBodyFactory;
use Psr\Http\Message\StreamFactoryInterface;

class StreamHelper
{
    public static function fromBodyRepository(StreamFactoryInterface $streamFactory, BodyRepository $bodyRepository, MultipartBodyFactory $multipartBodyFactory): StreamInterface
    {
        //
    }
}
