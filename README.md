<p align="center">
  <img src="./docs/skillshare-logo.svg" alt="Skillshare" width="100" />
</p>

<h1 align="center">FormatPHP</h1>

<p align="center">
    <a href="https://github.com/skillshare/formatphp"><img src="https://img.shields.io/badge/source-skillshare/formatphp-blue.svg" alt="Source Code"></a>
    <a href="https://github.com/Skillshare/formatphp/actions/workflows/continuous-integration.yml"><img src="https://github.com/Skillshare/formatphp/actions/workflows/continuous-integration.yml/badge.svg" alt="Build Status"></a>
    <a href="https://codeclimate.com/repos/61787bb74596dc01a300042c/maintainability"><img src="https://api.codeclimate.com/v1/badges/ea0d6112b63107b0bd40/maintainability" /></a>
    <a href="https://codeclimate.com/repos/61787bb74596dc01a300042c/test_coverage"><img src="https://api.codeclimate.com/v1/badges/ea0d6112b63107b0bd40/test_coverage" /></a>
</p>

<p align="center">
    <strong>A library to help internationalize PHP apps.</strong>
  <br />
  <sub>Made with ❤️ by <a href="https://skillshare.com">Skillshare Engineering</a></sub>
</p>

## About

Inspired by [FormatJS](https://formatjs.io) and
[ECMAScript 2023 Internationalization API (ECMA-402)](https://tc39.es/ecma402/),
this library provides an API to format dates, numbers, and strings, including
pluralization and translation. FormatPHP is powered by PHP's
[intl extension](https://www.php.net/intl) and integrates with [Unicode CLDR](http://cldr.unicode.org/)
and [ICU Message syntax](https://unicode-org.github.io/icu/userguide/format_parse/messages)
standards. It requires [libicu](https://icu.unicode.org) version 69.1 or higher.

This project adheres to a [code of conduct](CODE_OF_CONDUCT.md).
By participating in this project and its community, you are expected to
uphold this code.

## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

``` bash
composer require skillshare/formatphp
```

## Usage

For usage details and examples, see the full documentation at
[docs.formatphp.dev](https://docs.formatphp.dev).

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.

## Copyright and License

The skillshare/formatphp library is copyright © [Skillshare, Inc.](https://www.skillshare.com)
and licensed for use under the terms of the
Apache License, Version 2.0 (Apache-2.0). Please see [LICENSE](LICENSE) and
[NOTICE](NOTICE) for more information.
