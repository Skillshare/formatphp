# skillshare/formatphp Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 0.4.0 - 2022-01-21

### Added

- Add `Intl\NumberFormatOptions` to allow users to configure number string formatting.
- Add `Intl\DateTimeFormatOptions` to allow users to configure date and time string formatting.
- Provide functionality for formatting dates and times through `Intl\DateTimeFormat`, as well as `FormatPHP::formatDate()` and `FormatPHP::formatTime()` convenience methods.
- Add `UnableToFormatStringException` from which other formatting exceptions will descend.
- Add `UnableToFormatDateTimeException` thrown when we're unable to format a date or time string.
- Allow instantiation of `FormatPHP` without configuration or message collection instances; FormatPHP will use the system's default locale, in this case.
  - Instantiation of `Intl\Locale` without a locale argument will default to the system default locale.
  - Instantiation of `Config` without a locale argument will create an `Intl\Locale` using the system default locale.

### Changed

- Update `UnableToFormatMessageException` to descend from `UnableToFormatStringException`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.3 - 2022-01-14

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Normalize the locale file name before searching for it in `MessageLoader`, to account for differences in case, as well as filesystem case sensitivity (e.g. "en-XB" vs. "en_xb")

## 0.3.2 - 2021-12-17

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Check the contents of the file before parsing, to see if any of the formatting functions exist; if not, skip parsing the file

## 0.3.1 - 2021-12-17

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed case where errors occurring during flattening would cause `null` to be passed to a method that does not accept `null`
- Fixed case where "0" character caused a truthy check to fail, since `0 == false`

## 0.3.0 - 2021-12-16

### Added

- Add [Crowdin](https://crowdin.com) as a format for writing and reading extracted messages
- Add `pseudo-locale` console command to allow conversion of a locale to one of the supported pseudo-locales (`en-XA`, `en-XB`, `xx-AC`, `xx-HA`, and `xx-LS`).
- Provide `--flatten` extraction option to tell the extractor to hoist selectors and flatten sentences as much as possible. For example, `I have {count, plural, one{a dog} other{many dogs}}` becomes `{count, plural, one{I have a dog} other{I have many dogs}}`. The goal is to provide as many full sentences as possible, since fragmented sentences are not translator-friendly.
- Provide `--validate-messages` extraction option to print a list of validation failures and respond with a non-zero exit code on validation failures
- Provide `--add-missing-ids` extraction option to update source code with auto-generated identifiers
- Add `Util\FormatHelper` that provides `getReader()` and `getWriter()` methods
- Introduce `Format\Format` final static class for format constants
- Port [@formatjs/icu-messageformat-parser](https://www.npmjs.com/package/@formatjs/icu-messageformat-parser) to FormatPHP (`FormatPHP\Icu\MessageFormat\Parser`)

### Changed

- The `Extractor\MessageExtractor` constructor now requires `Util\FormatHelper` as a fifth parameter
- Remove `$config` argument from `Format\ReaderInterface`
- Remove `$localeResolved` argument from `Format\ReaderInterface`
- Change type on `$options` argument in `Format\WriterInterface` from `MessageExtractorOptions` to a dedicated `WriterOptions` type
- The `MessageLoader` constructor now accepts the following values for the `$formatReader` parameter:
  - Fully-qualified class name for a class that implements `FormatPHP\Format\ReaderInterface`
  - An already-instantiated instance object of `FormatPHP\Format\ReaderInterface`
  - A callable with the shape `callable(mixed[]): FormatPHP\MessageCollection`
  - The path to a script that returns a callable with this shape

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Support rich text formatting in the [same manner as FormatJS](https://formatjs.io/docs/core-concepts/icu-syntax#rich-text-formatting). Previously, we allowed HTML tags with attributes, etc., but this limits our ability to provide pseudo-locales and validation.

## 0.2.2 - 2021-11-18

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Parse only string literals or concatenated string literals

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
