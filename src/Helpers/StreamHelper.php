<?php

namespace Saloon\Helpers;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Contracts\MultipartBodyFactory;

class StreamHelper
{
    public static function fromBodyRepository(StreamFactoryInterface $streamFactory, BodyRepository $bodyRepository, MultipartBodyFactory $multipartBodyFactory): StreamInterface
    {
        //
    }
}
