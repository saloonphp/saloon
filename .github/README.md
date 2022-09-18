![1752](https://user-images.githubusercontent.com/29132017/149842636-e9964b27-7ace-4af9-a6db-23c325505295.jpg)

<div align="center">

# 🚪 Saloon 🚪

A Laravel / PHP package that helps you write beautiful API integrations and SDKs. It introduces a standardised, fluent syntax to communicate with third party services and has out of the box support for Laravel.

![Build Status](https://github.com/sammyjo20/saloon/actions/workflows/tests.yml/badge.svg)
    
[Click here to read the documentation](https://docs.saloon.dev)

</div>

## Introduction
Building API integrations can be difficult. Once you have found the right API client to use, there's often lots of boilerplate configuration to remember and decision fatigue can settle in quickly. You may have even found yourself abstracting your API integrations into actions or service classes to avoid repeating yourself. Saloon offers a fluent framework for building your next API integration or PHP SDK. Saloon's syntax is object-oriented, standardised and easy to understand.

If you're using Laravel, there's also a dedicated Laravel package with pre-written commands and a more advanced mocking toolbelt.

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
- Framework agnostic
- Mocking requests for testing
- Great for building your own PHP SDKs
- Authentication & OAuth2 boilerplate already built for you
- Scalable with lots of API integrations across many team members

## Documentation

[Click here to read the documentation](https://docs.saloon.dev)

## Contributing

Please see [here](https://github.com/Sammyjo20/Saloon/blob/main/.github/CONTRIBUTING.md) for more details about contributing.

## Security

Please see [here](https://github.com/Sammyjo20/Saloon/blob/main/.github/SECURITY.md) for our security policy.

## Banner Image Credit

- Freepik.com

## Support This Project

<a href='https://ko-fi.com/sammyjo20' target='_blank'><img height='35' style='border:0px;height:46px;' src='https://az743702.vo.msecnd.net/cdn/kofi3.png?v=0' border='0' alt='Buy Me a Coffee at ko-fi.com' />
