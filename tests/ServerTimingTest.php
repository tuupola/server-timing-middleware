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

namespace Tuupola\Middleware;

use Equip\Dispatch\MiddlewareCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use Tuupola\Http\Factory\ServerRequestFactory;
use Tuupola\Http\Factory\ResponseFactory;
use Tuupola\Middleware\ServerTiming\Stopwatch;

class ServerTimingTest extends TestCase
{
    public function testShouldBeTrue()
    {
        $this->assertTrue(true);
    }

    public function testShouldHandlePsr7()
    {
        $request = (new ServerRequestFactory)
            ->createServerRequest("GET", "https://example.com/");

        $response = (new ResponseFactory)->createResponse();

        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write("Success");
            return $response;
        };

        $timing = new ServerTimingMiddleware;
        $response = $timing($request, $response, $next);

        $header = $response->getHeader("Server-Timing")[0];
        $regex = "/Bootstrap;dur=[0-9\.]+, Process;dur=[0-9\.]+, Total;dur=[0-9\.]+/";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Success", $response->getBody());
        $this->assertTrue((boolean) preg_match($regex, $header));
    }

    public function testShouldHandlePsr15()
    {
        $request = (new ServerRequestFactory)
            ->createServerRequest("GET", "https://example.com/");

        $default = function (ServerRequestInterface $request) {
            $response = (new ResponseFactory)->createResponse();
            $response->getBody()->write("Success");
            return $response;
        };

        $collection = new MiddlewareCollection([
            new ServerTimingMiddleware
        ]);

        $response = $collection->dispatch($request, $default);

        $header = $response->getHeader("Server-Timing")[0];
        $regex = "/Bootstrap;dur=[0-9\.]+, Process;dur=[0-9\.]+, Total;dur=[0-9\.]+/";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Success", $response->getBody());
        $this->assertTrue((boolean) preg_match($regex, $header));
    }

    /* https://tools.ietf.org/html/rfc7230#section-3.2.6 */
    public function testShouldGenerateValidToken()
    {
        $request = (new ServerRequestFactory)
            ->createServerRequest("GET", "https://example.com/");

        $response = (new ResponseFactory)->createResponse();

        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write("Success");
            return $response;
        };

        $stopwatch = new Stopwatch;
        $stopwatch->set("DB Server", 100);

        $timing = new ServerTimingMiddleware($stopwatch);
        $response = $timing($request, $response, $next);

        $header = $response->getHeader("Server-Timing")[0];
        $regexp = '/^dbserver;dur=100;desc="DB Server", Bootstrap;dur=[0-9]+, Process;dur=[0-9]+, Total;dur=[0-9]+/';

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Success", $response->getBody());
        $this->assertRegexp($regexp, $header);
    }

    public function testShouldAlterDefaults()
    {
        $request = (new ServerRequestFactory)
            ->createServerRequest("GET", "https://example.com/");

        $response = (new ResponseFactory)->createResponse();

        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write("Success");
            return $response;
        };

        $timing = new ServerTimingMiddleware(
            new Stopwatch,
            [
                "bootstrap" => "Startup",
                "process" => null,
                "total" => "Sum"
            ]
        );
        $response = $timing($request, $response, $next);

        $header = $response->getHeader("Server-Timing")[0];
        $regexp = '/^Startup;dur=[0-9]+, Sum;dur=[0-9]+/';

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Success", $response->getBody());
        $this->assertRegexp($regexp, $header);
    }
}
