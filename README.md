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
