# Saloon 🚪

*Interact with REST APIs with confidence and elegance.*

Saloon is a PHP package which introduces a class-based/OOP approach to building connections to APIs. Saloon introduces an easy to understand pattern to help you standardise the way you interact with third-party APIs, reduce repeated code (DRY) and lets you mock API requests for your tests.

```php
use App\Http\Saloon\Requests\GetPokemonRequest;

$request = new GetPokemonRequest(name: 'Piplup');

$response = $request->send();
$data = $response->json();
```

## Juicy Features
- Easy mocking/testing utilities out of the box.
- Simple and elegant syntax.
- Conforms to the PSR-7 standard.
- You don't have to interact with cURL/Http Facade/Guzzle.
- Lets you update API requests in one place.
- Easily extend with your own functionality.

## Using Laravel?
Saloon has a powerful Laravel package. Check out sammyjo20/laravel-saloon to get started.

## Getting Started
To install Saloon, use Composer to install it into your PHP app.
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
