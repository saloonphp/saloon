<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

trait HasXMLBody
{
    /**
     * Add the headers to send XML
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public static function bootHasXMLBody(PendingSaloonRequest $request): void
    {
        $request->headers()->add('Accept', 'application/xml');
        $request->headers()->add('Content-Type', 'application/xml');
    }
}
