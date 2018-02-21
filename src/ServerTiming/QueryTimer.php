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

use Doctrine\DBAL\Logging\SQLLogger;

class QueryTimer implements SQLLogger
{
    public $stopwatch;

    public function __construct(StopwatchInterface $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }

    public function startQuery($sql, array $params = null, array $types = null): void
    {
        $this->stopwatch->start("SQL");
    }

    public function stopQuery(): void
    {
        $this->stopwatch->stop("SQL");
    }
}
