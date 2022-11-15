<?php

// Todo: Other Tests

use Saloon\Tests\Fixtures\Requests\HeaderRequest;

test('when merging properties if withoutConnectorHeaders was used it will not merge the headers in PendingSaloonRequest', function () {
    $request = new HeaderRequest();

    $request->mergeOptions()
        ->withoutConnectorHeaders();

    $request->headers()->set([
        'X-Foo' => 'Bar',
    ]);

    dd($request->createPendingRequest()->headers());
});
