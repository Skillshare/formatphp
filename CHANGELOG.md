# skillshare/formatphp Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 0.2.1 - 2021-11-17

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Do not throw exceptions when reading empty files

## 0.2.0 - 2021-11-16

### Added

- Use a ParserErrorCollection instead of array to pass errors through to the parsers for appending errors
- Provide `--parser` option to the extract command to allow custom parsers, in addition to the default `php` parser

### Changed

- Remove `DescriptorParserInterface::parse()`
- Add `DescriptorParserInterface::__invoke()`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2021-11-15

### Added

- Initial release of base functionality:
  - `FormatPHP\FormatPHP::formatMessage()` to format messages
  - Message extraction from application source code through `formatphp extract`
    console command
  - Message loading of locale messages in 3 formats: FormatPHP, Simple, and
    Smartling
  - `FormatPHP\Intl\Locale` and `FormatPHP\Intl\MessageFormat` for basic
    conformance with [ECMA-402](https://tc39.es/ecma402/) and
    [FormatJS](https://formatjs.io)

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
