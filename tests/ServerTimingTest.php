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
