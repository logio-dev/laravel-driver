<?php

namespace Logio;

use Illuminate\Support\ServiceProvider;

class LogioServiceProvider extends ServiceProvider
{
    public function provides(): array
    {
        return [LogioHandler::class];
    }
}