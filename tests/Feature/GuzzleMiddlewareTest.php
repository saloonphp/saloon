<?php

use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can add middleware to the guzzle sender', function () {
    $request = new UserRequest();

    dd($request->requestSender());
});
