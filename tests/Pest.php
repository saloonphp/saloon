<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

use Saloon\Http\Faking\MockClient;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function apiUrl()
{
    return 'https://tests.saloon.dev/api';
}

function wsdlUrl()
{
    return 'https://www.w3schools.com/xml/tempconvert.asmx?WSDL';
}

function connector(): TestConnector
{
    return new TestConnector;
}

function paginationMockClient(string $prefix): MockClient
{
    return new MockClient([
        '*' => function (PendingRequest $pendingRequest) use ($prefix) {
            $query = http_build_query($pendingRequest->query()->all());

            return MockResponse::fixture($prefix . '-' . $query);
        },
    ]);
}

/**
 * @returns array{
 *     id: int<1, max>,
 *     superhero: string,
 *     publisher: string,
 *     alter_ego: string,
 *     first_appearance: string,
 *     characters: string,
 * }
 */
function superheroes(): array
{
    return json_decode(file_get_contents(__DIR__ . '/Fixtures/Static/superheroes.json'), true, 512, JSON_THROW_ON_ERROR);
}
