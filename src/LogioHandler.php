<?php

namespace Logio;

use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class LogioHandler extends AbstractHandler
{
    private const ENDPOINT = 'https://api.logio.dev';

    private string $apiKey;

    public function __construct(string $key, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->apiKey = $key;
    }

    public function handle(array $record): bool
    {
        $response = Http::post(sprintf('%s/%s', self::ENDPOINT, $this->apiKey), $record);

        return $response->successful();
    }
}