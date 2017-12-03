#  PSR-7 and PSR-15 Server Timing Middleware

[![Latest Version](https://img.shields.io/packagist/v/tuupola/server-timing-middleware.svg?style=flat-square)](https://packagist.org/packages/tuupola/server-timing-middleware)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/tuupola/server-timing-middleware/master.svg?style=flat-square)](https://travis-ci.org/tuupola/server-timing-middleware)
[![Coverage](http://img.shields.io/codecov/c/github/tuupola/server-timing-middleware.svg?style=flat-square)](https://codecov.io/github/tuupola/server-timing-middleware)

This middleware implements the [Server-Timing](http://wicg.github.io/server-timing/) header which can be used for displaying server side timing information on Chrome DevTools.

![Server Timing](http://www.appelsiini.net/img/server-timing-1400.png)


## Install

Install using [Composer](https://getcomposer.org/):

``` bash
$ composer require tuupola/server-timing-middleware
```

## Usage

Example below assumes you are using [Slim](https://www.slimframework.com/). Note that `ServerTiming` must be added as last middleware. Otherwise timings will be inaccurate.

By default the middleware adds three timings:
1. `Bootstrap` is the time taken from start of the request to execution of the first incoming middleware
2. `Process` is the time taken for server to generate the response and process the middleware stack
3. `Total` is the total time taken

You can add your own timings by using the `Stopwatch` instance. See example below.


```php
require __DIR__ . "/vendor/autoload.php";

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Tuupola\Middleware\ServerTiming;
use Tuupola\Middleware\ServerTiming\Stopwatch;

$app = new \Slim\App;
$container = $app->getContainer();

$container["stopwatch"] = function ($container) {
    return new Stopwatch;
};

$container["ServerTiming"] = function ($container) {
    return new ServerTiming($container["stopwatch"]);
};

$container["DummyMiddleware"] = function ($container) {
    return function ($request, $response, $next) {
        usleep(200000);
        return $next($request, $response);
    };
};

$app->add("DummyMiddleware");
$app->add("ServerTiming");

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
```

```
$ curl --include http://0.0.0.0:8080/test

HTTP/1.1 200 OK
Host: 0.0.0.0:8080
Date: Tue, 07 Mar 2017 11:58:57 +0000
Connection: close
X-Powered-By: PHP/7.1.2
Content-Type: text/html; charset=UTF-8
Server-Timing: Bootstrap=9, externalapi=101; "External API", Magic=50, SQL=34, Process=360, Total=369
Content-Length: 0
```

## Usage with Doctrine DBAL

If you use Doctrine DBAL you can automate SQL query timings by using the provided `QueryTimer`. It implements the DBAL `SQLLogger` interface and can be used as standalone or in a `LoggerChain`. You must use the same `Stopwatch` instance with both `QueryTimer` and `ServerTiming` middleware.

```php
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\DBAL\Logging\LoggerChain;

use Tuupola\Middleware\ServerTiming\QueryTimer;
use Tuupola\Middleware\ServerTiming\Stopwatch;

$logger = new LoggerChain;
$echo = new EchoSQLLogger;
$stopwatch = new Stopwatch;
$timer = new QueryTimer($stopwatch);

$logger->addLogger($echo);
$logger->addLogger($timer);

/* Use your Doctrine DBAL connection here. */
$connection->getConfiguration()->setSQLLogger($logger);
```

## Testing

You can run tests either manually:

``` bash
$ make test
```
Or automatically on every code change. This requires [entr](http://entrproject.org/) to work:

``` bash
$ brew install entr
$ make watch
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email tuupola@appelsiini.net instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
