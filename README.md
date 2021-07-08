# Logio for Laravel
The official driver for Laravel, send logs to Logio.dev.

# Install
To start sending your logs to Logio follow the simple installation process below.

## Install with Composer

```
composer require logio/laravel-driver
```

## Set API Key
You need to set your API key in your .env file, you can grab your API key per application from https://logio.dev.

Example snippet of a .env file:
```
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true

LOG_CHANNEL=logio
LOGIO_API_KEY=secret

LOG_LEVEL=debug
```

Make sure you set your LOG_CHANNEL to "logio" as shown above.

### Add the driver to logging.php
Head over to your config/logging.php file and add logio as shown below.

```php
return [

    'channels' => [
        'logio' => [
            'driver' => 'monolog',
            'handler' => Logio\LogioHandler::class,
            'handler_with' => [
                'key' => env('LOGIO_API_KEY'),
                'channel' => env('APP_ENV'),
                'level' => env('LOG_LEVEL', 'debug'),
            ],
        ],    
    ],

];
```

## That's it
You're ready to go, start logging with the **Log** facade and watch your logs appear in Logio.dev.