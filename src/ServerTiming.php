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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tuupola\Middleware\ServerTiming\Stopwatch;

class ServerTiming
{
    private $stopwatch;
    private $bootstrap = "Bootstrap";
    private $process = "Process";
    private $total = "Total";

    public function __construct(Stopwatch $stopwatch = null)
    {
        if (null === $stopwatch) {
            $stopwatch = new Stopwatch;
        }
        $this->stopwatch = $stopwatch;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        /* REQUEST_TIME_FLOAT is closer to truth. */
        if (isset($_SERVER["REQUEST_TIME_FLOAT"])) {
            $start = $_SERVER["REQUEST_TIME_FLOAT"];
        } else {
            $start = microtime(true);
        }

        /* Time spent from starting the request to entering this middleware. */
        if ($this->bootstrap) {
            $bootstrap = (microtime(true) - $start) * 1000;
            $this->stopwatch->set($this->bootstrap, $bootstrap);
        }

        /* Call all the other middlewares. */
        if ($this->process) {
            $this->stopwatch->start($this->process);
            $response = $next($request, $response);
            $this->stopwatch->stop($this->process);
        }

        /* Time spent from starting the request to exiting last middleware. */
        if ($this->total) {
            $total = (microtime(true) - $start) * 1000;
            $this->stopwatch->set($this->total, (integer) $total);
        }

        $this->stopwatch->stopAll();

        $header = "";
        foreach ($this->stopwatch->values() as $key => $value) {
            $seconds = $value / 1000;
            $header .= sprintf("%s=%01.3f, ", $key, $seconds);
        };
        $header = preg_replace("/, $/", "", $header);

        return $response->withHeader("Server-Timing", $header);
        /*
        return $response->withHeader("Server-Timing", 'Total=0.120, cpu=0.009;
        "CPU", mysql=0.005; "MySQL", filesystem=0.006; "Filesystem"');
        */
    }

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;
        return $this;
    }

    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    public function setProcess($process)
    {
        $this->process = $process;
        return $this;
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }
}
