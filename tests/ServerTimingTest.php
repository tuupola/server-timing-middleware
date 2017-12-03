<?php

/*
 * This file is part of server timing middleware
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

namespace Tuupola\Middleware;

use Equip\Dispatch\MiddlewareCollection;
use Tuupola\Http\Factory\ServerRequestFactory;
use Tuupola\Http\Factory\ResponseFactory;
use Tuupola\Middleware\ServerTiming\Stopwatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerTimingTest extends \PHPUnit_Framework_TestCase
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

        $timing = new ServerTiming;
        $response = $timing($request, $response, $next);

        $header = $response->getHeader("Server-Timing")[0];
        $regex = "/Bootstrap=[0-9\.]+, Process=[0-9\.]+, Total=[0-9\.]+/";

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
            new ServerTiming
        ]);

        $response = $collection->dispatch($request, $default);

        $header = $response->getHeader("Server-Timing")[0];
        $regex = "/Bootstrap=[0-9\.]+, Process=[0-9\.]+, Total=[0-9\.]+/";

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

        $timing = new ServerTiming($stopwatch);
        $response = $timing($request, $response, $next);

        $header = $response->getHeader("Server-Timing")[0];
        $regex = '/^dbserver=100; "DB Server", Bootstrap=[0-9]+, Process=[0-9]+, Total=[0-9]+/';

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Success", $response->getBody());
        $this->assertTrue((boolean) preg_match($regex, $header));
    }
}
