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

namespace Tuupola\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tuupola\Middleware\DoublePassTrait;
use Tuupola\Middleware\ServerTiming\CallableHandler;
use Tuupola\Middleware\ServerTiming\Stopwatch;
use Tuupola\Middleware\ServerTiming\StopwatchInterface;

final class ServerTimingMiddleware implements MiddlewareInterface
{
    use DoublePassTrait;

    /**
     * @var StopwatchInterface
     */
    private $stopwatch;

    /**
     * @var float
     */
    private $start;

    /**
     * @var string|null
     */
    private $bootstrap = "Bootstrap";

    /**
     * @var string|null
     */
    private $process = "Process";

    /**
     * @var string|null
     */
    private $total = "Total";

    /**
     * @param mixed[] $options
     */
    public function __construct(?StopwatchInterface $stopwatch = null, array $options = [])
    {
        /* REQUEST_TIME_FLOAT is closer to truth. */
        $this->start = $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);

        if (null === $stopwatch) {
            $stopwatch = new Stopwatch();
        }
        $this->stopwatch = $stopwatch;

        /* Store passed in options overwriting any defaults. */
        $this->hydrate($options);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /* Time spent from starting the request to entering this middleware. */
        if ($this->bootstrap) {
            $bootstrap = (microtime(true) - $this->start) * 1000;
            $this->stopwatch->set($this->bootstrap, (int) $bootstrap);
        }

        /* Call all the other middlewares. */
        if ($this->process) {
            $this->stopwatch->start($this->process);
        }
        $response = $handler->handle($request);
        if ($this->process) {
            $this->stopwatch->stop($this->process);
        }

        /* Time spent from starting the request to exiting last middleware. */
        if ($this->total) {
            $total = (microtime(true) - $this->start) * 1000;
            $this->stopwatch->set($this->total, (int) $total);
        }
        $this->stopwatch->stopAll();

        return $response->withHeader(
            "Server-Timing",
            $this->generateHeader($this->stopwatch->values())
        );
    }

    /**
     * @param int[] $values
     */
    private function generateHeader(array $values): string
    {
        /* https://tools.ietf.org/html/rfc7230#section-3.2.6 */
        $regex = "/[^[:alnum:]!#$%&\'*\/+\-.^_`|~]/";
        $header = "";
        foreach ($values as $description => $timing) {
            if (preg_match($regex, $description)) {
                $token = preg_replace($regex, "", $description);
                if (null !== $token) {
                    $token = strtolower(trim($token, "-"));
                    $header .= sprintf('%s;dur=%d;desc="%s", ', $token, $timing, $description);
                }
            } else {
                $header .= sprintf("%s;dur=%d, ", $description, $timing);
            }
        };
        return $header = (string) preg_replace("/, $/", "", $header);
    }

    /**
     * Hydrate all options from the given array.
     *
     * @param mixed[] $data
     */
    private function hydrate(array $data = []): void
    {
        foreach ($data as $key => $value) {
            /* https://github.com/facebook/hhvm/issues/6368 */
            $key = str_replace(".", " ", $key);
            $method = "set" . ucwords($key);
            $method = str_replace(" ", "", $method);
            if (method_exists($this, $method)) {
                /* Try to use setter */
                /** @phpstan-ignore-next-line */
                call_user_func([$this, $method], $value);
            } else {
                /* Or fallback to setting option directly */
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Set description for bootstrap or null to disable.
     */
    private function setBootstrap(?string $bootstrap): void
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * Set description for process or null to disable.
     */
    private function setProcess(?string $process): void
    {
        $this->process = $process;
    }

    /**
     * Set description for total or null to disable.
     */
    private function setTotal(?string $total): void
    {
        $this->total = $total;
    }
}
