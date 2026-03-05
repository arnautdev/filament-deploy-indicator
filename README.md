# Show application ENV and latest deployment info inside your Filament admin.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arnautdev/filament-deploy-indicator.svg?style=flat-square)](https://packagist.org/packages/arnautdev/filament-deploy-indicator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/arnautdev/filament-deploy-indicator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/arnautdev/filament-deploy-indicator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/arnautdev/filament-deploy-indicator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/arnautdev/filament-deploy-indicator/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/arnautdev/filament-deploy-indicator.svg?style=flat-square)](https://packagist.org/packages/arnautdev/filament-deploy-indicator)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require arnautdev/filament-deploy-indicator
```

> [!IMPORTANT]
> If you have not set up a custom theme and are using Filament Panels follow the instructions in the [Filament Docs](https://filamentphp.com/docs/4.x/styling/overview#creating-a-custom-theme) first.

After setting up a custom theme add the plugin's views to your theme css file or your app's css file if using the standalone packages.

```css
@source '../../../../vendor/arnautdev/filament-deploy-indicator/resources/**/*.blade.php';
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-deploy-indicator-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-deploy-indicator-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-deploy-indicator-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentDeployIndicator = new Arnautdev\FilamentDeployIndicator();
echo $filamentDeployIndicator->echoPhrase('Hello, Arnautdev!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Dmitry Arnaut](https://github.com/arnautdev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
