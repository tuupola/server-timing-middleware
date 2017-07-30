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

namespace Tuupola\Middleware\ServerTiming;

use Closure;

interface StopwatchInterface
{
    public function start($key);

    public function stop($key);

    public function stopAll();

    public function closure($key, Closure $function = null);

    public function set($key, $value = null);

    public function get($key);

    public function stopwatch();

    public function memory();

    public function values();
}
