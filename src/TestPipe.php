<?php

namespace Sammyjo20\Saloon;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class TestPipe
{
    public function __invoke(PendingSaloonRequest $request)
    {
        $request->headers()->set(['X-Name' => 'Zac']);
    }
}
