<p align="center"><img src="/art/header.png" alt="Logo with brown western bar doors with western scene in background and text that says: Saloon, Your Lone Star of your API integrations"></p>

<div align="center">

# Saloon – Your Lone Star of your API integrations

A Laravel/PHP package that helps you build beautiful API integrations and SDKs 🤠

![Build Status](https://github.com/sammyjo20/saloon/actions/workflows/tests.yml/badge.svg)

[Click here to read the documentation](https://docs.saloon.dev)

</div>

## Introduction
Saloon is a PHP library that provides you with a beautiful API integration framework. It gives you with all the tools you need to build and test API integrations for your application or SDKs. It can be easily customised with plugins, but It comes pre-configured for you so you can get to sending API requests right away. Saloon comes with many exciting features out of the box like recording requests in your tests, caching, OAuth2 and pagination. 

```php
<?php

$forge = new ForgeConnector('api-token');

$response = $forge->send(new GetServersRequest);
$data = $response->json();
```

## Key Features

- Provides a simple, easy-to-learn, object-oriented syntax that standardises the way you interact with APIs
- No HTTP client configuration is required but can be completely customised if you need
- Abstract API integrations into classes to keep your code tidy and centralised
- Great for building your next PHP SDKs or package/library
- Works great within a team as it provides a standard everyone can follow
- Application/framework agnostic
- Packed full of features like request recording, request concurrency, caching, data-transfer-object support, and full Laravel support.
- Fully extendable and welcomes your own implementations
- Uses Guzzle, the most popular PHP HTTP client

## Getting Started

[Click here to get started](https://docs.saloon.dev/getting-started/installation)

## Contributing

Please see [here](../.github/CONTRIBUTING.md) for more details about contributing.

## Security

Please see [here](../.github/SECURITY.md) for our security policy.

## Credits

- [Sam Carré](https://github.com/Sammyjo20)
- [All Contributors](https://github.com/Sammyjo20/Saloon/contributors)

And a special thanks to [Caneco](https://twitter.com/caneco) for the logo ✨

## Support This Project

<a href='https://ko-fi.com/sammyjo20' target='_blank'><img height='35' style='border:0px;height:46px;' src='https://az743702.vo.msecnd.net/cdn/kofi3.png?v=0' border='0' alt='Buy Me a Coffee at ko-fi.com' />
