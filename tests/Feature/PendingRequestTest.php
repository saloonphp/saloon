<?php declare(strict_types=1);

// Todo: Other Tests

use Saloon\Tests\Fixtures\Requests\HeaderRequest;

test('when merging properties if withoutConnectorHeaders was used it will not merge the headers in PendingSaloonRequest', function () {
    $request = new HeaderRequest();

    $request->headers()->set(['X-Foo' => 'Bar']);

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->headers()->all())->toEqual([
        'Accept' => 'application/json', // Added by a plugin
        'X-Connector-Header' => 'Sam', // Merged default from connector
        'X-Foo' => 'Bar', // Header added to the request.
    ]);

    // Now we'll disable the merging

    $request->mergeOptions()->withoutConnectorHeaders();

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->headers()->all())->toEqual([
        'Accept' => 'application/json',
        'X-Foo' => 'Bar',
    ]);

    $request->send();
});
