<?php

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Symfony\Component\DomCrawler\Crawler;
use Sammyjo20\Saloon\Exceptions\SaloonRequestException;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can get the original request options', function () {
    $mockClient = new MockClient([
        MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
    ]);

    $response = (new UserRequest())->send($mockClient);

    $options = $response->getRequestOptions();

    expect($options)->toBeArray();
    expect($options['headers'])->toEqual(['Accept' => 'application/json']);
});

test('you can get the original request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
    ]);

    $request = new UserRequest;
    $response = $request->send($mockClient);

    expect($response->getOriginalRequest())->toBe($request);
});

test('it will throw an exception when you use the throw method', function () {
    $mockClient = new MockClient([
        MockResponse::make([], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);

    $this->expectException(SaloonRequestException::class);

    $response->throw();
});

test('it wont throw an exception if the request did not fail', function () {
    $mockClient = new MockClient([
        MockResponse::make([], 200),
    ]);

    $response = (new UserRequest())->send($mockClient);

    expect($response)->throw()->toBe($response);
});

test('to exception will return a saloon request exception', function () {
    $mockClient = new MockClient([
        MockResponse::make([], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $exception = $response->toException();

    expect($exception)->toBeInstanceOf(SaloonRequestException::class);
});

test('to exception wont return anything if the request did not fail', function () {
    $mockClient = new MockClient([
        MockResponse::make([], 200),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $exception = $response->toException();

    expect($exception)->toBeNull();
});

test('the onError method will run a custom closure', function () {
    $mockClient = new MockClient([
        MockResponse::make([], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $count = 0;

    $response->onError(function () use (&$count) {
        $count++;
    });

    expect($count)->toBe(1);
});

test('the object method will return an object', function () {
    $data = ['name' => 'Sam', 'work' => 'Codepotato'];

    $mockClient = new MockClient([
        MockResponse::make($data, 500),
    ]);

    $response = (new UserRequest())->send($mockClient);

    $dataAsObject = (object)$data;

    expect($response)->object()->toEqual($dataAsObject);
});

test('the collect method will return a collection', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $collection = $response->collect();

    expect($collection)->toBeInstanceOf(Collection::class);
    expect($collection)->toHaveCount(2);
    expect($collection['name'])->toEqual('Sam');
    expect($collection['work'])->toEqual('Codepotato');

    expect($response->collect('name'))->toArray()->toEqual(['Sam']);
    expect($response->collect('age'))->toBeEmpty();
});

test('the toGuzzleResponse and toPsrResponse methods will return a guzzle response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);

    expect($response)->toGuzzleResponse()->toBeInstanceOf(Response::class);
    expect($response)->toPsrResponse()->toBeInstanceOf(Response::class);
});

test('you can get an individual header from the response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 200, ['X-Greeting' => 'Howdy']),
    ]);

    $response = (new UserRequest())->send($mockClient);

    expect($response)->header('X-Greeting')->toEqual('Howdy');
    expect($response)->header('X-Missing')->toBeEmpty();
});

test('it will convert the body to string if the cast is used', function () {
    $data = ['name' => 'Sam', 'work' => 'Codepotato'];

    $mockClient = new MockClient([
        MockResponse::make($data, 200, ['X-Greeting' => 'Howdy']),
    ]);

    $response = (new UserRequest())->send($mockClient);

    expect((string)$response)->toEqual(json_encode($data));
});

test('it checks statuses correctly', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 200, ['X-Greeting' => 'Howdy']),
        MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 500, ['X-Greeting' => 'Howdy']),
        MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 302, ['X-Greeting' => 'Howdy']),
    ]);

    $responseA = (new UserRequest())->send($mockClient);

    expect($responseA)->successful()->toBeTrue();
    expect($responseA)->ok()->toBeTrue();
    expect($responseA)->redirect()->toBeFalse();
    expect($responseA)->failed()->toBeFalse();
    expect($responseA)->serverError()->toBeFalse();

    $responseB = (new UserRequest())->send($mockClient);

    expect($responseB)->successful()->toBeFalse();
    expect($responseB)->ok()->toBeFalse();
    expect($responseB)->redirect()->toBeFalse();
    expect($responseB)->failed()->toBeTrue();
    expect($responseB)->serverError()->toBeTrue();

    $responseC = (new UserRequest())->send($mockClient);

    expect($responseC)->successful()->toBeFalse();
    expect($responseC)->ok()->toBeFalse();
    expect($responseC)->redirect()->toBeTrue();
    expect($responseC)->failed()->toBeFalse();
    expect($responseC)->serverError()->toBeFalse();
});

test('the xml method will return xml as an array', function () {
    $mockClient = new MockClient([
        new MockResponse('<SaveContactResponse xmlns="http://schemas.datacontract.org/2004/07/SmashFly.WebServices.ContactManagerService.v2"><ContactId>1168255</ContactId><Errors nil="true" xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays"/><HasErrors>false</HasErrors></SaveContactResponse>', 200),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $simpleXml = $response->xml();

    expect($simpleXml)->toBeInstanceOf(SimpleXMLElement::class);
});

test('the dom method will return a crawler instance', function () {
    $dom = '<p>Howdy <i>Partner</i></p>';

    $mockClient = new MockClient([
        new MockResponse($dom, 200),
    ]);

    $response = (new UserRequest())->send($mockClient);

    expect($response->dom())->toBeInstanceOf(Crawler::class);
    expect($response->dom())->toEqual(new Crawler($dom));
});
