![1752](https://user-images.githubusercontent.com/29132017/149842636-e9964b27-7ace-4af9-a6db-23c325505295.jpg)

<div align="center">

# 🚪 Saloon 🚪

A Laravel / PHP package that helps you build beautiful API integrations and SDKs.

![Build Status](https://github.com/sammyjo20/saloon/actions/workflows/tests.yml/badge.svg)
    
[Click here to read the documentation](https://docs.saloon.dev)

</div>

## Introduction
Saloon offers a fluent, object-oriented wrapper to build your next API integration or PHP SDK. It makes sharing API requests throughout your application a breeze. You don’t have to configure a HTTP client, so you can start sending requests really quickly.

If you need request faking for your tests, Saloon has it out of the box alongside many other useful tools like OAuth2 boilerplate and caching. If you use Laravel, there's also a dedicated Laravel package with artisan console commands to help you build even faster.

```php
<?php

use App\Http\Saloon\Requests\GetForgeServerRequest;

$request = new GetForgeServerRequest(serverId: '123456');

$response = $request->send();
$data = $response->json();
```

## Features

- Simple and easy to learn syntax that standardises the way you interact with APIs
- Abstract API integrations into classes so you can keep your code DRY
- Configuration is fast and can be shared across all your requests
- Built on top of Guzzle, one of the most popular PHP HTTP clients.
- Framework agnostic
- Mocking requests for testing
- Great for building your own PHP SDKs
- Authentication & OAuth2 boilerplate already built for you
- Scalable with lots of API integrations across many team members

## Documentation

[Click here to read the documentation](https://docs.saloon.dev)

## Why Saloon?

Building API integrations can be time consuming. After you have found an API client to use, you’re faced with lots of configuration to remember and it’s hard to repeat requests without copying and pasting, and then when you introduce patterns like OAuth2 everything gets complicated. You’ll often find yourself writing the same boilerplate code over and over again. 

We’ve standardised the way we talk to APIs with PSR-7 and PSR-18 but we haven’t got a standard structure to build API integrations.

Saloon aims to solve this.

## Contributing

Please see [here](https://github.com/Sammyjo20/Saloon/blob/main/.github/CONTRIBUTING.md) for more details about contributing.

## Security

Please see [here](https://github.com/Sammyjo20/Saloon/blob/main/.github/SECURITY.md) for our security policy.

## Banner Image Credit

- Freepik.com

## Support This Project

<a href='https://ko-fi.com/sammyjo20' target='_blank'><img height='35' style='border:0px;height:46px;' src='https://az743702.vo.msecnd.net/cdn/kofi3.png?v=0' border='0' alt='Buy Me a Coffee at ko-fi.com' />
