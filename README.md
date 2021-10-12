# Saloon

Interact with REST APIs with confidence and elegance.

Saloon is a PHP package which introduces a class-based/OOP approach to interacting with APIs. Saloon introduces an easy to understand pattern to help you write cleaner code, reduce repeated requests and standardise how you interact with APIs.

> Laravel Artisan? Saloon has a Laravel package with built in commands. Check out sammyjo20/laravel-saloon to get started.

```php
use App\Http\Saloon\Requests\GetPokemonRequest;

$request = new GetPokemonRequest(name: 'Piplup');

$response = $request->send();
$data = $response->json();
```
