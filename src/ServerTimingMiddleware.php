<?php

/*
 * This file is part of server timing middleware
 *
 * Copyright (c) 2017 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * See also:
 *   https://github.com/tuupola/server-timing-middleware
 *   https://w3c.github.io/server-timing/
 *
 */

namespace Tuupola\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tuupola\Middleware\ServerTiming\CallableHandler;
use Tuupola\Middleware\ServerTiming\Stopwatch;
use Tuupola\Middleware\ServerTiming\StopwatchInterface;
use Tuupola\Middleware\DoublePassTrait;

final class ServerTimingMiddleware implements MiddlewareInterface
{
    use DoublePassTrait;

    private $stopwatch;
    private $start;
    private $bootstrap = "Bootstrap";
    private $process = "Process";
    private $total = "Total";

    public function __construct(StopwatchInterface $stopwatch = null, array $options = [])
    {
        /* REQUEST_TIME_FLOAT is closer to truth. */
        $this->start = $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);

        if (null === $stopwatch) {
            $stopwatch = new Stopwatch;
        }
        $this->stopwatch = $stopwatch;

        /* Store passed in options overwriting any defaults. */
        $this->hydrate($options);
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

    private function generateHeader(array $values): string
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

    /**
     * Hydrate all options from the given array.
     */
    private function hydrate(array $data = []): void
    {
        foreach ($data as $key => $value) {
            /* https://github.com/facebook/hhvm/issues/6368 */
            $key = str_replace(".", " ", $key);
            $method = "set" . ucwords($key);
            $method = str_replace(" ", "", $method);
            if (method_exists($this, $method)) {
                /* Try to use setter */
                call_user_func([$this, $method], $value);
            } else {
                /* Or fallback to setting option directly */
                $this->options[$key] = $value;
            }
        }
    }

    /**
     * Set description for bootstrap or null to disable.
     */
    private function setBootstrap(?string $bootstrap): void
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * Set description for process or null to disable.
     */
    private function setProcess(?string $process): void
    {
        $this->process = $process;
    }

    /**
     * Set description for total or null to disable.
     */
    private function setTotal(?string $total): void
    {
        $this->total = $total;
    }
}
