# RPCs for Laravel
Directly call procedures from other Laravel services with Auto-discovery. Best for Microservices built with Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arffsaad/lrpc.svg?style=flat-square)](https://packagist.org/packages/arffsaad/lrpc)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/arffsaad/lrpc/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/arffsaad/lrpc/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/arffsaad/lrpc/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/arffsaad/lrpc/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/arffsaad/lrpc.svg?style=flat-square)](https://packagist.org/packages/arffsaad/lrpc)

Use LRPC to define RPCs in your microservices to enable easy cross-service communications. This package was heavily inspired during my time at RunCloud, and I this is what I envisioned would be the perfect way to call different methods from another service with the least amount of friction, and with the least amount of tinkering between multiple services.

> This README is still WIP!!!

## Installation

You can install the package via composer:

```bash
composer require arffsaad/lrpc
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="lrpc-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="lrpc-views"
```

## Usage

```php
$lRPC = new ArffSaad\LRPC();
echo $lRPC->echoPhrase('Hello, ArffSaad!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ariff Saad](https://github.com/arffsaad)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
