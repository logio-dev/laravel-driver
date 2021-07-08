<?php

namespace Tests;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Logio\LogioHandler;
use Monolog\DateTimeImmutable;
use Monolog\Logger;
use Orchestra\Testbench\TestCase;

class LogioHandlerTest extends TestCase
{
    private const ENDPOINT = 'https://api.logio.dev';

    protected function getRecord(int $level = Logger::WARNING, string $message = 'test', array $context = []): array
    {
        return [
            'message' => (string) $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'test',
            'datetime' => new DateTimeImmutable(true),
            'extra' => [],
        ];
    }

    public function test_can_send_event(): void
    {
        Http::fake(
            [
                self::ENDPOINT . '/my-app-key' => Http::response(null, Response::HTTP_ACCEPTED, []),
            ]
        );

        $driver = new LogioHandler('my-app-key');
        $result = $driver->handle($this->getRecord(Logger::DEBUG, 'a debug message', ['request-id' => 'foo']));
        $this->assertTrue($result);
    }

    public function test_can_handle_internal_server_error(): void
    {
        Http::fake(
            [
                self::ENDPOINT . '/my-app-key' => Http::response(null, Response::HTTP_INTERNAL_SERVER_ERROR, []),
            ]
        );

        $driver = new LogioHandler('my-app-key');
        $result = $driver->handle($this->getRecord(Logger::DEBUG, 'a debug message', ['request-id' => 'foo']));
        $this->assertFalse($result);
    }
}