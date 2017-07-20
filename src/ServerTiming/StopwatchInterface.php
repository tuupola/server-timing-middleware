<?php


namespace Tuupola\Middleware\ServerTiming;


use Closure;

interface StopwatchInterface
{
    public function start($key);

    public function stop($key);

    public function stopAll();

    public function closure($key, Closure $function = NULL);

    public function set($key, $value = NULL);

    public function get($key);

    public function stopwatch();

    public function memory();

    public function values();
}