# Saloon 🚪🚪

*Interact with REST APIs with elegance.*

Saloon is a PHP package which introduces a class-based/OOP approach to building connections to APIs. Saloon introduces an easy to understand pattern to help you standardise the way you interact with third-party APIs, reduce repeated code (DRY) and lets you mock API requests for your tests.

> Note: Saloon is still a work in progress, some features aren't quite ready yet but are coming soon.

```php
<?php

use App\Http\Saloon\Requests\GetForgeServerRequest;

$request = new GetForgeServerRequest(serverId: '123456');

$response = $request->send();
$data = $response->json();
```

## Features
- Simple and elegant syntax.
- Standardises the way you interact with APIs.
- Conforms to the PSR-7 standard.
- You don't have to interact with cURL/Http Facade/Guzzle.
- Lets you update API requests in one place.
- Easily extend with your own functionality.
- Framework agnostic.
- Mocking requests for testing. (work in progress)

> Note on mocking/faking: I'm currently looking for some help with mocking requests in Saloon. If you have any suggestions to make this better, please consider contributing to the issue on the issues page.

## Getting Started
### Using Laravel?
There is a version of Saloon for Laravel, to install it use Composer.
```bash
composer require sammyjo20/saloon-laravel
```
Otherwise, to install Saloon, use Composer to install it into your PHP app.
```bash
composer require sammyjo20/saloon
```
> Saloon requires PHP 8

## Connectors
Once you have installed Saloon, the first thing you want to create is a "Connector". Connectors are classes where you define an APIs basic requirements. Within a connector, you can define the URL of the API, default headers and even pass in your own functionality which is shared across all a connection's requests. You should have a separate connector for each API integration.
> If you are using Laravel, you can use the **php artisan saloon:connector** command.

Let's have a look at our ForgeConnector. As you can see, the bare minimum you must define is a base url. 
```php
<?php

use Sammyjo20\Saloon\Http\SaloonConnector;

class ForgeConnector extends SaloonConnector
{
    /**
     * Define the base url for the connector.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return 'https://forge.laravel.com/api/v1';
    }
}
```
You can also specify default headers and configuration options which will be applied to every request.
```php
<?php

use Sammyjo20\Saloon\Http\SaloonConnector;

class ForgeConnector extends SaloonConnector
{
    /**
     * Define the base url for the connector.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return 'https://forge.laravel.com/api/v1';
    }
    
    /**
     * Define the base headers for the connector.
     *
     * @return string[]
     */
    public function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.forge.key') // "config" is a built in Laravel function.
        ];
    }
    
    /**
     * Define the default Guzzle configuration for the connector.
     *
     * @return string[]
     */
    public function defaultConfig(): array
    {
        // You can specify any of the Guzzle configuration options here.
        // See https://docs.guzzlephp.org/en/stable/request-options.html for more.
    
        return [
            'timeout' => 5,
        ];
    }
}
```
## Requests
The second most important file in Saloon is your request. Requests are where you define each method of the API you want to call. The minimum requirements are `$method`. `$connector` and `defineMethod()`.

> If you are using Laravel, you can use the **php artisan saloon:request** command.

Let's have a look at our GetForgeServerRequest.
```php
<?php

use App\Http\Saloon\Connectors\ForgeConnector;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;

class GetForgeServerRequest extends SaloonRequest
{
    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected ?string $method = Saloon::GET;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected ?string $connector = ForgeConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/servers/' . $this->serverId;
    }
    
    /**
     * Constructor, you can pass in your own properties.
     *
     */
    public function __construct(
        public string $serverId
    ){}
}
```

Requests can also have their own default headers and configuration which are merged in with the connector's default headers and configuration. These will take priority over connector's default values.
```php
<?php

use App\Http\Saloon\Connectors\ForgeConnector;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;

class GetForgeServerRequest extends SaloonRequest
{
    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected ?string $method = Saloon::GET;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected ?string $connector = ForgeConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/servers/' . $this->serverId;
    }
    
    /**
     * Define the base headers for the connector.
     *
     * @return string[]
     */
    public function defaultHeaders(): array
    {
        return [
            'X-Custom-Header' => 'Hello-World',
        ];
    }
    
    /**
     * Define the default Guzzle configuration for the connector.
     *
     * @return string[]
     */
    public function defaultConfig(): array
    {
        // You can specify any of the Guzzle configuration options here.
        // See https://docs.guzzlephp.org/en/stable/request-options.html for more.
    
        return [
            'query' => [
                'filter' => 'onlyActive',
            ],
        ];
    }
    
    public function __construct(
        public string $serverId
    ){}
}
```
## Making your request
Once you have created your Saloon Connector and Request, you are ready to make your request! 

Here's a simple example of making a request.
```php
<?php

use App\Http\Saloon\Requests\GetForgeServerRequest;

$request = new GetForgeServerRequest(serverId: '123456');

$response = $request->send();
```
You can also set/overwrite any configuration at this stage too! Any headers/configuration added at this stage are merged in with the default values from the connector and request, but will take the highest priority.
```php
<?php

use App\Http\Saloon\Requests\GetForgeServerRequest;

$request = new GetForgeServerRequest(serverId: '123456');

$request->addHeader('Accept', 'application/json');
$request->addConfig('debug', true);

$request->setHeaders($array); // This will overwrite all default headers.
$request->setConfig($array); // This will overwrite all default configration options.

$response = $request->send();
```
## API responses
Once Saloon has sent the request, you will be given an instance of `SaloonResponse` to easily interact with the response from the server.
```php
$response = $request->send();
```
### Available methods
The Saloon response has a lot of handy methods for you. A lot of these are taken from `Illuminate/Http`.
```php
getSaloonRequestOptions(): array
toPsrResponse(): ResponseInterface
body(): string
json(): array
object(): object
collect(): Collection
header(): string
headers(): array
getStatusFroMResponse(): int
status(): int
effectiveUri(): UriInterface
successful(): bool
ok(): bool
redirect(): bool
failed(): bool
clientError(): bool
serverError(): bool
onError(callable $callback)
cookies(): CookieJar
handlerStats(): array
toException()
throw()
```

## Saloon Plugins
Saloon also comes with a library of useful "plugins" in the form of traits. These plugins can be added to either the Connector or a Request, depending on if you want the plugin to be used on all requests within a connection or just one request.

Plugins can add their own headers/Guzzle configuration. These are especially useful if you have headers that you frequently want to add to a specific connection or request. For example the `Content-Type: application/json` header.

Let's take a look at the `AcceptsJson` feature plugin. We will add it to our ForgeConnector.
```php
<?php

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\Features\AcceptsJson;

class ForgeConnector extends SaloonConnector
{
    use AcceptsJson;

    /**
     * Define the base url for the connector.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return 'https://forge.laravel.com/api/v1';
    }
}
```
Now let's take a look inside the `AcceptsJson` feature plugin. As you can see, we are using the `mergeHeaders($headers)` so it adds its own headers.
```php
<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait AcceptsJson
{
    public function bootAcceptsJsonFeature()
    {
        $this->mergeHeaders([
            'Accept' => 'application/json'
        ]);
    }
}
```
This plugin will add the header "Accept: application/json". These headers take a lower priority than the "defaultHeaders" defined in the Request/Connector.

### Available plugins
- AcceptsJson
- DisablesSSLVerification
- HasBody
- HasJsonBody
- HasMultipartBody
- HasQueryParams
- HasTimeout
- WithDebugData

### Todo
- Glossary and separate pages
- Example on a simple GET request
- Example on a simple POST request with request data
- Examples on the features
