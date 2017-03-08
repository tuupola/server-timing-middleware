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

use Zend\Diactoros\ServerRequest as Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

use Equip\Dispatch\MiddlewarePipe;

class ServerTimingTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeTrue()
    {
        $this->assertTrue(true);
    }

    public function testShouldHandlePsr7()
    {
        $request = (new Request())
            ->withUri(new Uri("https://example.com/"))
            ->withMethod("GET");

        $response = new Response;

        $next = function (Request $request, Response $response) {
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
        $request = (new Request())
            ->withUri(new Uri("https://example.com/"))
            ->withMethod("GET");

        $default = function (Request $request) {
            $response = new Response;
            $response->getBody()->write("Success");
            return $response;
        };

        $pipe = new MiddlewarePipe([
            new ServerTiming
        ]);
        $response = $pipe->dispatch($request, $default);

        $header = $response->getHeader("Server-Timing")[0];
        $regex = "/Bootstrap=[0-9\.]+, Process=[0-9\.]+, Total=[0-9\.]+/";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Success", $response->getBody());
        $this->assertTrue((boolean) preg_match($regex, $header));
    }
}
