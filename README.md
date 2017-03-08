#  PSR-7 Server Timing Middleware

[![Latest Version](https://img.shields.io/packagist/v/tuupola/server-timing-middleware.svg?style=flat-square)](https://packagist.org/packages/tuupola/server-timing-middleware)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/tuupola/server-timing-middleware/master.svg?style=flat-square)](https://travis-ci.org/tuupola/server-timing-middleware)
[![HHVM Status](https://img.shields.io/hhvm/tuupola/server-timing-middleware.svg?style=flat-square)](http://hhvm.h4cc.de/package/tuupola/server-timing-middleware)
[![Coverage](http://img.shields.io/codecov/c/github/tuupola/server-timing-middleware.svg?style=flat-square)](https://codecov.io/github/tuupola/server-timing-middleware)

This middleware implements the [Server-Timing](http://wicg.github.io/server-timing/) header. This additional server info can be seen for example in Chrome devtools timing view.

## Usage

Install using [composer](https://getcomposer.org/).

``` bash
$ composer require tuupola/server-timing-middleware
```

```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Tuupola\Middleware\ServerTiming;
use Tuupola\Middleware\ServerTiming\Stopwatch;

$app = new \Slim\App;

$app->add(function ($request, $response, $next) {
    usleep(200000);
	return $next($request, $response);
});

$stopwatch = new StopWatch;
$app->add(new ServerTiming($stopwatch));

$app->get("/test", function (Request $request, Response $response) use ($stopwatch) {
    $stopwatch->start("MySQL");
    usleep(150000);
    $stopwatch->stop("MySQL");

    $stopwatch->closure("API", function() {
        usleep(100000);
    });

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
Server-Timing: Bootstrap=0.010, MySQL=0.155, API=0.101, Process=0.463, Total=0.473
Content-Length: 0
```

## Testing

You can run tests either manually...

``` bash
$ composer test
```

... or automatically on every code change. This requires [entr](http://entrproject.org/) to work.

``` bash
$ composer watch
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email tuupola@appelsiini.net instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
