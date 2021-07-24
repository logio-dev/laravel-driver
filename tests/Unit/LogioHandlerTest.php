<?php

use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Logio\LogioHandler;
use Monolog\Logger;

it(
    'can send single log event when buffer limit is zero',
    function () {
        Http::fake(
            [
                'api.logio.dev' => Http::response(null, Response::HTTP_ACCEPTED),
            ],
        );

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0),
            ],
        );
        $logger->debug('hello world');

        Http::assertSentCount(1);
    }
);

it(
    'can send single log event when buffer limit is one',
    function () {
        Http::fake(
            [
                'api.logio.dev' => Http::response(null, Response::HTTP_ACCEPTED),
            ],
        );

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 1),
            ],
        );
        $logger->debug('hello world');

        Http::assertSentCount(1);
    }
);

it(
    'does not send when buffer limit is greater than total events',
    function () {
        Http::fake();

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 2),
            ],
        );
        $logger->debug('hello world');

        Http::assertNothingSent();
    }
);

it(
    'does send multiple requests if buffer limit reached',
    function () {
        Http::fake(
            [
                'api.logio.dev' => Http::response(null, Response::HTTP_ACCEPTED),
            ],
        );

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 2),
            ],
        );
        $logger->debug('hello world');
        $logger->debug('hello world');

        Http::assertSentCount(2);
    }
);

it(
    'does clear buffer after flushed',
    function () {
        Http::fake(
            [
                'api.logio.dev' => Http::response(null, Response::HTTP_ACCEPTED),
            ],
        );

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0),
            ],
        );
        $logger->debug('hello world');
        $logger->debug('hello world');

        Http::assertSentCount(2);
    }
);

it(
    'does contain valid message and level in request',
    function () {
        Http::fake();

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0),
            ],
        );
        $logger->debug('hello world');

        Http::assertSent(
            function (Request $request) {
                $data = $request->data();
                return $data['message'] === 'hello world'
                    && $data['level'] === Logger::DEBUG
                    && $data['level_name'] === Logger::getLevelName(Logger::DEBUG)
                    && $data['channel'] === 'tester'
                    && $data['context'] === [];
            }
        );
    }
);

it(
    'does contain level INFO in request',
    function () {
        Http::fake();

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0),
            ],
        );
        $logger->info('hello world');

        Http::assertSent(
            function (Request $request) {
                $data = $request->data();
                return $data['message'] === 'hello world'
                    && $data['level'] === Logger::INFO
                    && $data['level_name'] === Logger::getLevelName(Logger::INFO)
                    && $data['channel'] === 'tester'
                    && $data['context'] === [];
            }
        );
    }
);

it(
    'does contain context in request',
    function () {
        Http::fake();

        $logger = new Logger(
            'tester',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0),
            ],
        );
        $logger->debug('hello world', ['request-id' => 'foo-bar']);

        Http::assertSent(
            function (Request $request) {
                $data = $request->data();
                return $data['message'] === 'hello world'
                    && $data['level'] === Logger::DEBUG
                    && $data['level_name'] === Logger::getLevelName(Logger::DEBUG)
                    && $data['channel'] === 'tester'
                    && $data['context'] === ['request-id' => 'foo-bar'];
            }
        );
    }
);

it(
    'does contain correct channel in request',
    function () {
        Http::fake();

        $logger = new Logger(
            'production',
            [
                new LogioHandler(API_KEY, Logger::DEBUG, 0, 'production'),
            ],
        );
        $logger->debug('hello world');

        Http::assertSent(
            function (Request $request) {
                $data = $request->data();
                return $data['message'] === 'hello world'
                    && $data['level'] === Logger::DEBUG
                    && $data['level_name'] === Logger::getLevelName(Logger::DEBUG)
                    && $data['channel'] === 'production'
                    && $data['context'] === [];
            }
        );
    }
);