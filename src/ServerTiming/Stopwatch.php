<?php

/*
 * This file is part of server timing middleware
 *
 * Copyright (c) 2017-2018 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * See also:
 *   https://github.com/tuupola/server-timing-middleware
 *   https://w3c.github.io/server-timing/
 *
 */

namespace Tuupola\Middleware\ServerTiming;

use Closure;
use Symfony\Component\Stopwatch\Stopwatch as SymfonyStopWatch;

class Stopwatch implements StopwatchInterface
{
    private $stopwatch = null;
    private $memory = null;
    private $keys = [];
    private $values = [];

    public function __construct()
    {
        $this->stopwatch = new SymfonyStopWatch;
    }

    public function start($key): StopwatchInterface
    {
        $this->stopwatch->start($key);
        array_push($this->keys, $key);
        return $this;
    }

    public function stop($key): StopwatchInterface
    {
        if ($this->stopwatch->isStarted($key)) {
            $event = $this->stopwatch->stop($key);
            $duration = $event->getDuration();
            $this->memory = $event->getMemory();
            $this->set($key, $duration);
        }
        return $this;
    }

    public function stopAll(): StopwatchInterface
    {
        foreach ($this->keys as $key) {
            $this->stop($key);
        }
        return $this;
    }

    public function closure($key, Closure $function = null)
    {
        $this->start($key);
        $return = $function();
        $this->stop($key);
        return $return;
    }

    public function set($key, $value = null): StopwatchInterface
    {
        /* Allow calling $timing->set("fly", function () {...}) */
        if ($value instanceof Closure) {
            $this->closure($key, $value);
        } else {
            $this->values[$key] = $value;
        }
        return $this;
    }

    public function get($key): ?int
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
        return null;
    }

    public function stopwatch(): SymfonyStopWatch
    {
        return $this->stopwatch;
    }

    public function memory(): ?int
    {
        return $this->memory;
    }

    public function values(): array
    {
        return $this->values;
    }
}
