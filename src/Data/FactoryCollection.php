<?php

declare(strict_types=1);

namespace Saloon\Data;

use Psr\Http\Message\UriFactoryInterface;
use Saloon\Contracts\MultipartBodyFactory;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

readonly class FactoryCollection
{
    /**
     * Constructor
     *
     * This class is used to collect all the different PSR and Saloon factories
     * together into one, simple class that can be defined by senders.
     */
    public function __construct(
        public RequestFactoryInterface  $requestFactory,
        public UriFactoryInterface      $uriFactory,
        public StreamFactoryInterface   $streamFactory,
        public ResponseFactoryInterface $responseFactory,
        public MultipartBodyFactory     $multipartBodyFactory,
    ) {
        //
    }
}
