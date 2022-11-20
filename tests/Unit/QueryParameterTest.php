<?php

declare(strict_types=1);

use Saloon\Managers\RequestManager;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterConnectorRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterConnectorBlankRequest;
use Saloon\Tests\Fixtures\Requests\OverwrittenQueryParameterConnectorRequest;

test('a request with query params added sends the query params', function () {
    $request = new QueryParameterRequest();

    $request->addQuery('sort', '-created_at');

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('query');
    expect($config['query'])->toHaveKey('per_page', 100); // Test Default
    expect($config['query'])->toHaveKey('sort', '-created_at'); // Test Adding Data After
});

test('if setQuery is used, all other default query params wont be included', function () {
    $request = new QueryParameterConnectorRequest();

    $request->setQuery([
        'sort' => 'nickname',
    ]);

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('query');
    expect($config['query'])->toEqual(['sort' => 'nickname']);
});

test('a connector can have query that is set', function () {
    $request = new QueryParameterConnectorRequest();

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('query');
    expect($config['query'])->toHaveKey('sort', 'first_name'); // Added by connector
    expect($config['query'])->toHaveKey('include', 'user'); // Added by request
});

test('a request query parameter can overwrite a connectors parameter', function () {
    $request = new OverwrittenQueryParameterConnectorRequest();

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('query');
    expect($config['query'])->toEqual(['sort' => 'date_of_birth']);
});

test('manually overwriting query parameter in runtime can overwrite connector parameter', function () {
    $request = new QueryParameterConnectorBlankRequest();

    $request->addQuery('sort', 'custom_field');

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('query');
    expect($config['query'])->toEqual(['sort' => 'custom_field']);
});

test('manually overwriting query parameter in runtime can overwrite request parameter', function () {
    $request = new QueryParameterRequest();

    $request->addQuery('per_page', 500);

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('query');
    expect($config['query'])->toEqual(['per_page' => 500]);
});

test('when not sending query parameters, the query option is not set', function () {
    $request = new UserRequest();

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect(isset($config['query']))->toBeFalse();
});
