<?php

/*

Copyright (c) 2017-2020 Mika Tuupola

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

use Doctrine\DBAL\Logging\SQLLogger;

class QueryTimer implements SQLLogger
{
    /**
     * @var StopwatchInterface
     */
    public $stopwatch;

    public function __construct(StopwatchInterface $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * @param mixed[] $params
     * @param mixed[] $types
     */
    public function startQuery($sql, array $params = null, array $types = null): void
    {
        $this->stopwatch->start("SQL");
    }

    public function stopQuery(): void
    {
        $this->stopwatch->stop("SQL");
    }
}
