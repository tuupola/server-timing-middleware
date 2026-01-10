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

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

/**
 * @phpstan-type OverrideParams = array{
 *     application_name?: string,
 *     charset?: string,
 *     dbname?: string,
 *     defaultTableOptions?: array<string, mixed>,
 *     driver?: key-of<\Doctrine\DBAL\DriverManager::DRIVER_MAP>,
 *     driverClass?: class-string<\Doctrine\DBAL\Driver>,
 *     driverOptions?: array<mixed>,
 *     host?: string,
 *     memory?: bool,
 *     password?: string,
 *     path?: string,
 *     persistent?: bool,
 *     port?: int,
 *     serverVersion?: string,
 *     sessionMode?: int,
 *     user?: string,
 *     unix_socket?: string,
 *     wrapperClass?: class-string<\Doctrine\DBAL\Connection>,
 * }
 * @phpstan-type Params = array{
 *     application_name?: string,
 *     charset?: string,
 *     dbname?: string,
 *     defaultTableOptions?: array<string, mixed>,
 *     driver?: key-of<\Doctrine\DBAL\DriverManager::DRIVER_MAP>,
 *     driverClass?: class-string<\Doctrine\DBAL\Driver>,
 *     driverOptions?: array<mixed>,
 *     host?: string,
 *     keepReplica?: bool,
 *     memory?: bool,
 *     password?: string,
 *     path?: string,
 *     persistent?: bool,
 *     port?: int,
 *     primary?: OverrideParams,
 *     replica?: array<OverrideParams>,
 *     serverVersion?: string,
 *     sessionMode?: int,
 *     user?: string,
 *     wrapperClass?: class-string<\Doctrine\DBAL\Connection>,
 *     unix_socket?: string,
 * }
 */
class QueryTimerDriver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $driver,
        private readonly StopwatchInterface $stopwatch
    ) {
        parent::__construct($driver);
    }

    /**
     * @param OverrideParams $params
     */
    public function connect(array $params): DriverConnection
    {
        return new QueryTimerConnection(
            parent::connect($params),
            $this->stopwatch
        );
    }
}
