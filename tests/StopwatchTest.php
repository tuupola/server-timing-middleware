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

use PHPUnit\Framework\TestCase;
use Tuupola\Middleware\ServerTiming\Stopwatch;

class StopwatchTest extends TestCase
{
    public function testShouldBeTrue()
    {
        $this->assertTrue(true);
    }

    public function testShouldGetAndSetValues()
    {
        $stopwatch = new Stopwatch;
        $this->assertNull($stopwatch->get("water"));
        $stopwatch->set("water", 100);
        $this->assertEquals(100, $stopwatch->get("water"));
    }

    public function testShouldReturnFromClosure()
    {
        $stopwatch = new Stopwatch;
        $value = $stopwatch->closure("name", function () {
            return "Not sure?";
        });
        $this->assertEquals("Not sure?", $value);
    }

    public function testShouldSetClosure()
    {
        $stopwatch = new Stopwatch;
        $this->assertNull($stopwatch->get("juice"));
        $stopwatch->set("juice", function () {
            usleep(50000);
        });
        $this->assertTrue($stopwatch->get("juice") > 0);
    }

    public function testShouldGetSymfonyStopWatch()
    {
        $stopwatch = new Stopwatch;
        $this->assertInstanceOf(
            "Symfony\Component\Stopwatch\Stopwatch",
            $stopwatch->stopwatch()
        );
    }

    public function testShouldGetMemory()
    {
        $stopwatch = new Stopwatch;
        $this->assertNull($stopwatch->memory());
        $stopwatch->start("run");
        $stopwatch->stop("run");
        $this->assertTrue($stopwatch->memory() > 0);
    }
}
