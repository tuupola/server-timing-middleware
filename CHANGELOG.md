# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## [0.8.0](https://github.com/tuupola/server-timing-middleware/compare/0.7.0...0.8.0) - 2018-04-24
### Changed
- New header format as implemented in Chrome 66 ([#5](https://github.com/tuupola/server-timing-middleware/issues/5)) ([#8](https://github.com/tuupola/server-timing-middleware/pull/8))
- Removed unused options from `Tuupola\Middleware\ServerTiming` constructor.


## [0.7.0](https://github.com/tuupola/server-timing-middleware/compare/0.6.0...0.7.0) - 2018-01-25
### Added
- Support for the [approved version of PSR-15](https://github.com/php-fig/http-server-middleware).

## [0.6.0](https://github.com/tuupola/server-timing-middleware/compare/0.5.0...0.6.0) - 2017-12-27
### Added
- Support for the [latest version of PSR-15](https://github.com/http-interop/http-server-middleware).
- Possibility to rename or disable default timings via options array.
    ```php
    $app->add(new ServerTimingMiddleware($stopwatch, [
        "bootstrap" => "Startup",
        "process" => null,
        "total" => "Sum"
    ]);
    ````

### Changed
- Classname changed from ServerTiming to ServerTimingMiddleware.
- ServerTimingMiddleware is now declared final.
- PSR-7 double pass is now supported via [tuupola/callable-handler](https://github.com/tuupola/callable-handler) library.
- PHP 7.1 is now minimum requirement.

### Removed
-  PSR-15 is now PHP 7.x only. Support for PHP 5.X was removed.

## [0.5.0](https://github.com/tuupola/server-timing-middleware/compare/0.4.0...0.5.0) - 2017-07-30
### Added
- StopwatchInterface to enable custom stopwatch implementations ([#3](https://github.com/tuupola/server-timing-middleware/pull/3)).

### Changed
- Stopwatch instance is now protected instead of private ([#3](https://github.com/tuupola/server-timing-middleware/pull/3)).

## [0.4.0](https://github.com/tuupola/server-timing-middleware/compare/0.3.0...0.4.0) - 2017-05-11
### Changed
- Values are now in [milliseconds]((https://codereview.chromium.org/2689833002)) as required by Chrome 58.

## [0.3.0](https://github.com/tuupola/server-timing-middleware/compare/0.3.0...0.2.0) - 2017-03-09
### Added
- `QueryTimer` class for Doctrine DBAL which can be used to automatically get SQL timings.

## 0.2.0 - 2017-03-08
Initial realese. Supports both PSR-7 and PSR-15 style middlewares. Both have unit tests. However PSR-15 has not really been tested in production.
