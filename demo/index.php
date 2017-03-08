<?php

require __DIR__ . "/vendor/autoload.php";


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Tuupola\Middleware\ServerTiming;
use Tuupola\Middleware\ServerTiming\Stopwatch;

$app = new \Slim\App([
    "addContentLengthHeader" => false,
]);

$app->add(function ($request, $response, $next) {
    usleep(200000);
    return $next($request, $response);
});

$stopwatch = new Stopwatch;
$app->add(new ServerTiming($stopwatch));

$app->get("/test", function (Request $request, Response $response) use ($stopwatch) {
    $stopwatch->start("MySQL");
    usleep(150000);
    $stopwatch->stop("MySQL");

    $stopwatch->closure("API (test)", function () {
        usleep(100000);
    });

    return $response;
});

$app->run();
