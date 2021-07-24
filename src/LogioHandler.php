<?php

namespace Logio;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerTrait;
use Monolog\Logger;

class LogioHandler implements HandlerInterface
{
    use ProcessableHandlerTrait;

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $level;

    /**
     * @var int
     */
    private $maxBuffer;

    /**
     * @var int
     */
    private $bufferSize;

    /**
     * @var array
     */
    private $buffer;

    /**
     * @var bool
     */
    private $initialised;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * LogioHandler constructor.
     *
     * @param  string  $key        Your API Key for your application, you can obtain this
     *                             key from https://logio.dev.
     * @param  int     $level      The log level at which to send events, for example
     *                             if you set the level to be Logger::INFO, only events
     *                             of INFO or higher will be sent to Logio.
     * @param  int     $maxBuffer  The maximum buffer size until the logs are sent
     *                             to Logio and reset.
     *
     * @param  string  $endpoint   The API endpoint for Logio.
     */
    public function __construct(
        string $key,
        int $level = Logger::DEBUG,
        int $maxBuffer = 0,
        string $endpoint = 'https://api.logio.dev'
    ) {
        $this->key         = $key;
        $this->level       = $level;
        $this->maxBuffer   = $maxBuffer;
        $this->bufferSize  = 0;
        $this->buffer      = [];
        $this->initialised = false;
        $this->endpoint    = $endpoint;
    }

    public function handle(array $record): bool
    {
        if ((int)$record['level'] < $this->level) {
            return false;
        }

        if (!$this->initialised) {
            $this->initialised = true;
        }

        if ($this->processors) {
            $record = $this->processRecord($record);
        }

        $this->buffer[] = $record;
        $this->bufferSize++;

        if ($this->bufferSize >= $this->maxBuffer) {
            $this->flush();
        }

        return true;
    }

    public function isHandling(array $record): bool
    {
        return true;
    }

    public function close(): void
    {
        $this->flush();
    }

    public function flush(): void
    {
        $this->handleBatch($this->buffer);
        $this->clear();
    }

    public function clear(): void
    {
        $this->bufferSize = 0;
        $this->buffer     = [];
    }

    public function handleBatch(array $records): void
    {
        Http::pool(
            function (Pool $pool) use ($records) {
                return array_map(
                    function (array $record) use ($pool) {
                        return $pool->post(sprintf('%s/%s', $this->endpoint, $this->key), $record);
                    },
                    $records,
                );
            }
        );
    }
}