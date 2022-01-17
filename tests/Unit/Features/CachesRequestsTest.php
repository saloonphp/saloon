<?php

use Sammyjo20\Saloon\Tests\Resources\Requests\CachedUserRequest;

test('it can return a cached request', function () {
    $request = new CachedUserRequest();

    $request->send();
});
