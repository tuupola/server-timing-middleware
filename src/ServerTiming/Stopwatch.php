<?php

/*

Copyright (c) 2017-2018 Mika Tuupola

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

/**
 * @see       https://github.com/tuupola/server-timing-middleware
 * @see       https://w3c.github.io/server-timing/
 * @license   https://www.opensource.org/licenses/mit-license.php
 */

namespace Tuupola\Middleware\ServerTiming;

use Closure;
use Symfony\Component\Stopwatch\Stopwatch as SymfonyStopWatch;

class Stopwatch implements StopwatchInterface
{
    /**
     * @var SymfonyStopWatch
     */
    private $stopwatch;

    /**
     * @var int
     */
    private $memory = null;

    /**
     * @var string[]
     */
    private $keys = [];

    /**
     * @var int[]
     */
    private $values = [];

    public function __construct()
    {
        $this->stopwatch = new SymfonyStopWatch;
    }

    public function start(string $key): StopwatchInterface
    {
        $this->stopwatch->start($key);
        array_push($this->keys, $key);
        return $this;
    }

    public function stop(string $key): StopwatchInterface
    {
        if ($this->stopwatch->isStarted($key)) {
            $event = $this->stopwatch->stop($key);
            $duration = $event->getDuration();
            $this->memory = $event->getMemory();
            $this->set($key, (int) $duration);
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

    /**
     * @return mixed
     */
    public function closure(string $key, Closure $function)
    {
        $this->start($key);
        $return = $function();
        $this->stop($key);
        return $return;
    }

    public function set(string $key, $value): StopwatchInterface
    {
        /* Allow calling $timing->set("fly", function () {...}) */
        if ($value instanceof Closure) {
            $this->closure($key, $value);
        } else {
            $this->values[$key] = $value;
        }
        return $this;
    }

    public function get(string $key): ?int
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

    /**
     * @return int[]
     */
    public function values(): array
    {
        return $this->values;
    }
}
