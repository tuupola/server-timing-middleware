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

namespace Tuupola\Middleware;

use PHPUnit\Framework\TestCase;
use Tuupola\Middleware\ServerTiming\QueryTimer;
use Tuupola\Middleware\ServerTiming\Stopwatch;

class QueryTimerTest extends TestCase
{
    public function testShouldBeTrue()
    {
        $this->assertTrue(true);
    }

    public function testShouldStartAndStopQueryTimer()
    {
        $stopwatch = new Stopwatch;
        $timer = new QueryTimer($stopwatch);
        $timer->startQuery("SELECT * FROM brawndos");
        usleep(10000);
        $timer->stopQuery();
        $this->assertArrayHasKey("SQL", $stopwatch->values());
    }
}
