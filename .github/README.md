<p align="center"><img src="/art/header.png" alt="Logo with brown western bar doors with western scene in background and text that says: Saloon, Your Lone Star of your API integrations"></p>

<div align="center">

# Saloon – Your Lone Star of your API integrations

A PHP package that helps you build beautiful API integrations and SDKs 🤠

[![Build Status](https://github.com/saloonphp/saloon/actions/workflows/tests.yml/badge.svg)](https://img.shields.io/github/actions/workflow/status/saloonphp/saloon/tests.yml?label=tests)
![Downloads](https://img.shields.io/packagist/dm/saloonphp/saloon)

[Click here to read the documentation](https://docs.saloon.dev)

</div>

## Introduction
Saloon is a PHP library that gives you the tools to build beautifully simple API integrations and SDKs. Saloon moves your API requests into reusable classes so you can keep all your API configurations in one place. Saloon comes with many exciting features out of the box like recording requests in your tests, caching, OAuth2 and pagination. It's a great starting point for building simple, standardised API integrations in your application.

```php
<?php

$forge = new ForgeConnector('api-token');

$response = $forge->send(new GetServersRequest);

$data = $response->json();
```

## Key Features

- Provides a simple, easy-to-learn, and modern way to build clean, reusable API integrations
- Built on top of Guzzle, the most popular and feature-rich HTTP client
- Works great within a team as it provides a standard everyone can follow
- Great for building your next PHP SDK or library
- Packed full of features like request recording, request concurrency, caching, data-transfer-object support, and full Laravel support.
- Framework agnostic
- Lightweight and has few dependencies.

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
