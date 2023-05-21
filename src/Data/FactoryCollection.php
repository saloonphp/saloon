<?php

namespace Saloon\Data;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Saloon\Contracts\MultipartBodyFactory;

class FactoryCollection
{
    /**
     * Constructor
     *
     * This class is used to collect all the different PSR and Saloon factories
     * together into one, simple class that can be defined by senders.
     *
     * @param RequestFactoryInterface $requestFactory
     * @param UriFactoryInterface $uriFactory
     * @param StreamFactoryInterface $streamFactory
     * @param MultipartBodyFactory $multipartBodyFactory
     */
    public function __construct(
        readonly public RequestFactoryInterface $requestFactory,
        readonly public UriFactoryInterface     $uriFactory,
        readonly public StreamFactoryInterface  $streamFactory,
        readonly public MultipartBodyFactory    $multipartBodyFactory,
    )
    {
        //
    }
}
