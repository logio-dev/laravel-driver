<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Logio\LogioHandler;
use Monolog\Logger;

it(
    'can send single log event when buffer limit is zero',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->debug('hello world');

        $mockClient->shouldHaveReceived('postAsync')->once();
    }
);

it(
    'can send single log event when buffer limit is one',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 1, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->debug('hello world');

        $mockClient->shouldHaveReceived('postAsync')->once();
    }
);

it(
    'does not send when buffer limit is greater than total events',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 2, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->debug('hello world');

        $mockClient->shouldNotHaveReceived('postAsync');
    }
);

it(
    'does send multiple requests if buffer limit reached',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 2, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->debug('hello world');
        $logger->debug('hello world');

        $mockClient->shouldHaveReceived('postAsync')->twice();
    }
);

it(
    'does clear buffer after flushed',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->debug('hello world');
        $logger->debug('hello world');

        $mockClient->shouldHaveReceived('postAsync')->twice();
    }
);

it(
    'does contain valid message and level in request',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->debug('hello world');

        $mockClient->shouldHaveReceived('postAsync')->once()->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                unset($options['json']['datetime']);

                return $options === [
                    'json' => [
                        'message' => 'hello world',
                        'context' => [],
                        'level' => Logger::DEBUG,
                        'level_name' => Logger::getLevelName(Logger::DEBUG),
                        'channel' => 'tester',
                        'extra' => [],
                    ]
                ];
            })
        );
    }
);

it(
    'does contain level INFO in request',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->info('hello world');

        $mockClient->shouldHaveReceived('postAsync')->once()->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                unset($options['json']['datetime']);

                return $options === [
                    'json' => [
                        'message' => 'hello world',
                        'context' => [],
                        'level' => Logger::INFO,
                        'level_name' => Logger::getLevelName(Logger::INFO),
                        'channel' => 'tester',
                        'extra' => [],
                    ]
                ];
            })
        );
    }
);

it(
    'does contain context in request',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->debug('hello world', ['request-id' => 'foo']);

        $mockClient->shouldHaveReceived('postAsync')->once()->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                unset($options['json']['datetime']);

                return $options === [
                    'json' => [
                        'message' => 'hello world',
                        'context' => ['request-id' => 'foo'],
                        'level' => Logger::DEBUG,
                        'level_name' => Logger::getLevelName(Logger::DEBUG),
                        'channel' => 'tester',
                        'extra' => [],
                    ]
                ];
            })
        );
    }
);

it(
    'does contain correct channel in request',
    function () {
        $mockClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $mockClient = Mockery::spy($mockClient);

        $logger = new Logger(
            'production',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0, API_ENDPOINT, $mockClient),
            ],
        );
        $logger->debug('hello world');

        $mockClient->shouldHaveReceived('postAsync')->once()->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                unset($options['json']['datetime']);

                return $options === [
                    'json' => [
                        'message' => 'hello world',
                        'context' => [],
                        'level' => Logger::DEBUG,
                        'level_name' => Logger::getLevelName(Logger::DEBUG),
                        'channel' => 'production',
                        'extra' => [],
                    ]
                ];
            })
        );
    }
);
