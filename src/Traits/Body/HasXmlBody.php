<?php

namespace Sammyjo20\Saloon\Traits\Body;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

trait HasXmlBody
{
    use HasBody;

    /**
     * Boot the plugin
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public function bootHasXmlBody(PendingSaloonRequest $request): void
    {
        $request->headers()->add('Accept', 'application/xml');
        $request->headers()->add('Content-Type', 'application/xml');
    }
}
