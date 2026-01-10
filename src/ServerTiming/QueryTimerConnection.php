<?php

/*
Copyright (c) 2017-2022 Mika Tuupola
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

use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Statement as DriverStatement;

class QueryTimerConnection extends AbstractConnectionMiddleware
{
    public function __construct(
        DriverConnection $connection,
        private readonly StopwatchInterface $stopwatch
    ) {
        parent::__construct($connection);
    }

    public function prepare(string $sql): DriverStatement
    {
        return new QueryTimerStatement(
            parent::prepare($sql),
            $this->stopwatch
        );
    }

    public function query(string $sql): \Doctrine\DBAL\Driver\Result
    {
        $this->stopwatch->start("SQL");
        try {
            return parent::query($sql);
        } finally {
            $this->stopwatch->stop("SQL");
        }
    }

    public function exec(string $sql): int|string
    {
        $this->stopwatch->start("SQL");
        try {
            return parent::exec($sql);
        } finally {
            $this->stopwatch->stop("SQL");
        }
    }
}
