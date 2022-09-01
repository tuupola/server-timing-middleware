<?php

/*
To test:

$ php -S 0.0.0:8081 index.php
$ curl http://localhost:8081/test --include
*/

require __DIR__ . "/vendor/autoload.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tuupola\Middleware\ServerTiming\Stopwatch;
use Tuupola\Middleware\ServerTimingMiddleware;

$app = new \Slim\App();
$container = $app->getContainer();

$container["stopwatch"] = function ($container) {
    return new Stopwatch();
};

$container["ServerTimingMiddleware"] = function ($container) {
    return new ServerTimingMiddleware($container["stopwatch"]);
};

$container["DummyMiddleware"] = function ($container) {
    return function ($request, $response, $next) {
        usleep(200000);
        return $next($request, $response);
    };
};

$app->add("DummyMiddleware");
$app->add("ServerTimingMiddleware");

$app->get("/test", function (Request $request, Response $response) {
    $this->stopwatch->start("External API");
    usleep(100000);
    $this->stopwatch->stop("External API");

    $this->stopwatch->closure("Magic", function () {
        usleep(50000);
    });

    $this->stopwatch->set("SQL", 34);

    return $response;
});

$app->run();
