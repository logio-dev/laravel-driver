<?php

namespace Logio\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Logio\LogioHandler;
use Monolog\Logger;

class FlushBufferMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request   $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  Request   $request
     * @param  Response  $response
     * @return void
     */
    public function terminate(Request $request, Response $response)
    {
        /** @var LogManager $manager */
        $manager = Log::getFacadeRoot();

        /** @var Logger $logger */
        $logger = $manager->driver($manager->getDefaultDriver());
        if (!empty($logger->getHandlers())) {
            $logio = $logger->getHandlers()[0];
            if ($logio instanceof LogioHandler) {
                $logio->close();
            }
        }
    }
}