![1752](https://user-images.githubusercontent.com/29132017/149842636-e9964b27-7ace-4af9-a6db-23c325505295.jpg)

<div align="center">

# 🚪 Saloon 🚪

A PHP package that helps you write beautiful API integrations. It introduces a standardised, fluent syntax to communicate with third party services and has out of the box support for Laravel.

![Build Status](https://github.com/sammyjo20/saloon/actions/workflows/tests.yml/badge.svg)
    
[Click here to read the documentation](https://docs.saloon.dev)

</div>

## Introduction

Building API integrations can be difficult. Once you have found the right API client to use, there's often lots of boilerplate configuration to remember and decision fatigue can settle in really quickly. Saloon offers a fresh, fluent framework for building your next API integration or PHP SDK. Saloon's syntax is object-oriented, standardised and easy to understand. You can build one or many API integrations with Saloon, the sky is the limit. If you're using Laravel, there's a dedicated Laravel package with pre-written commands and a more advanced mocking toolbelt. Saloon will help you get up and running with APIs in a matter of minutes, and will scale with your project.

```php
<?php

use App\Http\Saloon\Requests\GetForgeServerRequest;

$request = new GetForgeServerRequest(serverId: '123456');

$response = $request->send();
$data = $response->json();
```

## Features

- Simple, easy to learn syntax that standardises the way you interact with APIs
- You don't have to worry about Guzzle/Http Facade/cURL
- Framework agnostic
- Organise all your API integrations in one place
- Mocking requests for testing
- Great for building your own PHP SDKs
- Easily add on your own functionality with plugins
- Powerful interceptor logic to customise the response
- Customise everything under the hood with handlers and middleware
- Comes with a great Laravel package

## Documentation

[Click here to read the documentation](https://docs.saloon.dev)

## Contributing

Please see [here](https://github.com/Sammyjo20/Saloon/blob/main/.github/CONTRIBUTING.md) for more details about contributing.

## Security

Please see [here](https://github.com/Sammyjo20/Saloon/blob/main/.github/SECURITY.md) for our security policy.

## Banner Image Credit

- Freepik.com
