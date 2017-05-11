# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.4.0 - 2017-05-11
### Changed
- Values are now in [milliseconds]((https://codereview.chromium.org/2689833002)) as required by Chrome 58.

## 0.3.0 - 2017-03-09
### Added
- `QueryTimer` class for Doctrine DBAL which can be used to automatically get SQL timings.

## 0.2.0 - 2017-03-08
Initial realese. Supports both PSR-7 and PSR-15 style middlewares. Both have unit tests. However PSR-15 has not really been tested in production.
