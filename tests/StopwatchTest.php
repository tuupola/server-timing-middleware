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

    // public function testShouldReturnFromClosure()
    // {
    //     $stopwatch = new Stopwatch;
    //     $value = $stopwatch->closure("name", function () {
    //         return 6.66;
    //     });
    //     $this->assertEquals(6.66, $value);
    // }

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
