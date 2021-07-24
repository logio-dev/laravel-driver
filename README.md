# Logio for Laravel
The official driver for Laravel, send your application logs to [Logio.dev](https://logio.dev).

# Install
To start sending your logs to Logio follow the simple installation process below or read the guide posted on our blog at [https://logio.dev/blog/use-logio-with-laravel](https://logio.dev/blog/use-logio-with-laravel). 

## Install with Composer

```
composer require logio-dev/laravel-driver
```

## Add the driver
Head over to your config/logging.php file and add logio as shown below.

```php
return [

    'channels' => [
        'logio' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => Logio\LogioHandler::class,
            'handler_with' => [
                'key' => env('LOGIO_API_KEY'),
                'maxBuffer' => env('LOGIO_MAX_BUFFER', 0),
                'endpoint' => env('LOGIO_API_ENDPOINT', 'https://api.logio.dev'),
            ],
        ],  
    ],

];
```

## API Key and Settings
You need to set your API key in your .env file, you can grab your API key per application from https://logio.dev.

```dotenv
LOG_CHANNEL=logio
LOGIO_API_KEY=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

Make sure you set your **LOG_CHANNEL** to "logio" as shown above.

## Enable Asynchronous Sending
Out of the box Logio will record log events synchronously, this can be slow when you have a lot of logs to send. 

To mitigate this issue you can enable the **Logio\Http\Middleware\FlushBufferMiddleware** in your applications **app/Http/Kernel.php** as show below under the global middleware group.

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        
        \Logio\Http\Middleware\FlushBufferMiddleware::class,
    ];
```

Under the hood the **FlushBufferMiddleware** uses terminable middleware provided by Laravel, after your applications response has been sent Logio will send all logs asynchronously in batches of **LOGIO_MAX_BUFFER**.

Make sure you set **LOGIO_MAX_BUFFER** in your .env file as the default is 0 which means to send one at a time, the below will send batches of 10 logs asynchronously.

```dotenv
LOGIO_MAX_BUFFER=10
```

## That's it
You're ready to go, start logging with the **Log** facade and watch your logs appear in Logio.dev.