<?php

/*
 * This file is part of the server timing middleware
 *
 * Copyright (c) 2017 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/server-timing-middleware
 *
 */

namespace Tuupola\Middleware;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tuupola\Middleware\ServerTiming\CallableHandler;
use Tuupola\Middleware\ServerTiming\Stopwatch;
use Tuupola\Middleware\ServerTiming\StopwatchInterface;
use Tuupola\Middleware\DoublePassTrait;

class ServerTimingMiddleware implements MiddlewareInterface
{
    use DoublePassTrait;

    protected $stopwatch;
    private $start;
    private $bootstrap = "Bootstrap";
    private $process = "Process";
    private $total = "Total";

    public function __construct(StopwatchInterface $stopwatch = null)
    {
        /* REQUEST_TIME_FLOAT is closer to truth. */
        $this->start = $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);

        if (null === $stopwatch) {
            $stopwatch = new Stopwatch;
        }
        $this->stopwatch = $stopwatch;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /* Time spent from starting the request to entering this middleware. */
        if ($this->bootstrap) {
            $bootstrap = (microtime(true) - $this->start) * 1000;
            $this->stopwatch->set($this->bootstrap, $bootstrap);
        }

        /* Call all the other middlewares. */
        if ($this->process) {
            $this->stopwatch->start($this->process);
        }
        $response = $handler->handle($request);
        if ($this->process) {
            $this->stopwatch->stop($this->process);
        }

        /* Time spent from starting the request to exiting last middleware. */
        if ($this->total) {
            $total = (microtime(true) - $this->start) * 1000;
            $this->stopwatch->set($this->total, (integer) $total);
        }
        $this->stopwatch->stopAll();

        return $response->withHeader(
            "Server-Timing",
            $this->generateHeader($this->stopwatch->values())
        );
    }

    private function generateHeader(array $values)
    {
        /* https://tools.ietf.org/html/rfc7230#section-3.2.6 */
        $regex = "/[^[:alnum:]!#$%&\'*\/+\-.^_`|~]/";
        $header = "";
        foreach ($values as $description => $timing) {
            if (preg_match($regex, $description)) {
                $token = preg_replace($regex, "", $description);
                $token = strtolower(trim($token, "-"));
                $header .= sprintf('%s=%d; "%s", ', $token, $timing, $description);
            } else {
                $header .= sprintf("%s=%d, ", $description, $timing);
            }
        };
        return $header = preg_replace("/, $/", "", $header);
    }
}
