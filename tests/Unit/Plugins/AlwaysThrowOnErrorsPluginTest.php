<?php declare(strict_types=1);

use Sammyjo20\Saloon\Exceptions\SaloonRequestException;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\AlwaysThrowRequest;

test('it always throws an error if the plugin has been added', function () {
    $this->expectException(SaloonRequestException::class);

    (new AlwaysThrowRequest())->send();
});
