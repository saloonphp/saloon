<?php declare(strict_types=1);

use Saloon\Exceptions\SaloonRequestException;
use Saloon\Tests\Fixtures\Requests\AlwaysThrowRequest;

test('it always throws an error if the plugin has been added', function () {
    $this->expectException(SaloonRequestException::class);

    (new AlwaysThrowRequest())->send();
});
