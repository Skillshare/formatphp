<p align="center">
  <img src="./docs/skillshare-logo.svg" alt="Skillshare" width="100" />
</p>

<h1 align="center">FormatPHP</h1>

<p align="center">
    <a href="https://circleci.com/gh/Skillshare/formatphp"><img src="https://circleci.com/gh/Skillshare/formatphp/tree/main.svg?style=shield&circle-token=62f660cad385d565bd7626adb947380317fc19e0" alt="circleci" /></a>
    <a href="https://codeclimate.com/repos/61787bb74596dc01a300042c/maintainability"><img src="https://api.codeclimate.com/v1/badges/ea0d6112b63107b0bd40/maintainability" /></a>
    <a href="https://codeclimate.com/repos/61787bb74596dc01a300042c/test_coverage"><img src="https://api.codeclimate.com/v1/badges/ea0d6112b63107b0bd40/test_coverage" /></a>
    <!--
    <a href="https://github.com/skillshare/formatphp"><img src="https://img.shields.io/badge/source-skillshare/formatphp-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/skillshare/formatphp"><img src="https://img.shields.io/packagist/v/skillshare/formatphp.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/skillshare/formatphp.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/skillshare/formatphp/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/skillshare/formatphp.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
    <a href="https://github.com/skillshare/formatphp/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/workflow/status/skillshare/formatphp/build/main?style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/skillshare/formatphp"><img src="https://img.shields.io/codecov/c/gh/skillshare/formatphp?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a>
    <a href="https://shepherd.dev/github/skillshare/formatphp"><img src="https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Fskillshare%2Fformatphp%2Fcoverage" alt="Psalm Type Coverage"></a>
    -->
</p>

<p align="center">
    <strong>A library to help internationalize PHP apps.</strong>
  <br />
  <sub>Made with ❤️ by <a href="https://skillshare.com">Skillshare Engineering</a></sub>
</p>

## About

Inspired by [FormatJS](https://formatjs.io) and
[ECMAScript Internationalization API (ECMA-402)](https://www.ecma-international.org/ecma-402/),
this library provides an API to format dates, numbers, and strings, including
pluralization and handling translations. FormatPHP is powered by PHP's
[intl extension](https://www.php.net/intl) and integrates with [Unicode CLDR](http://cldr.unicode.org/)
and [ICU Message syntax](https://unicode-org.github.io/icu/userguide/format_parse/messages)
standards.

This project adheres to a [code of conduct](CODE_OF_CONDUCT.md).
By participating in this project and its community, you are expected to
uphold this code.

## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

``` bash
composer require skillshare/formatphp
```

## Usage

The following example shows a complete working implementation of FormatPHP.

```php
use FormatPHP\Config;
use FormatPHP\FormatPHP;
use FormatPHP\Intl;
use FormatPHP\Message;
use FormatPHP\MessageCollection;

// Translated messages in Spanish with matching IDs to what you declared.
$messagesInSpanish = new MessageCollection([
    new Message('hello', '¡Hola {name}! Hoy es {ts, date, ::yyyyMMdd}.'),
]);

$config = new Config(
    // Locale of the application (or of the user using the application).
    locale: new Intl\Locale('es'),
);

$formatphp = new FormatPHP(
    config: $config,
    messages: $messagesInSpanish,
);

echo $formatphp->formatMessage([
    'id' => 'hello',
    'defaultMessage' => 'Hello, {name}! Today is {ts, date, ::yyyyMMdd}.',
], [
    'name' => 'Arwen',
    'ts' => time(),
]);
```

### Rich Text Formatting (Use of Tags in Messages)

While the ICU message syntax does not prohibit the use of HTML tags in formatted
messages, HTML tags provide an added level of difficulty when it comes to parsing
and validating ICU formatted messages. By default, FormatPHP does not support
HTML tags in messages.

Instead, [like FormatJS](https://formatjs.io/docs/core-concepts/icu-syntax#rich-text-formatting),
we support embedded rich text formatting using custom tags and callbacks. This
allows developers to embed as much text as possible so sentences don't have to
be broken up into chunks. These are not HTML or XML tags, and attributes are
not supported.

```php
$formatphp->formatMessage([
    'id' => 'priceMessage',
    'defaultMessage' => <<<'EOD'
        Our price is <boldThis>{price, number, ::currency/USD precision-integer}</boldThis>
        with <link>{pct, number, ::percent} discount</link>
        EOD,
], [
    'price' => 29.99,
    'pct' => 2.5,
    'boldThis' => fn ($text) => "<strong>$text</strong>",
    'link' => fn ($text) => "<a href=\"/discounts/1234\">$text</a>",
]);
```

For an `en-US` locale, this will produce a string similar to the following:

    Our price is <strong>$30</strong> with <a href="/discounts/1234">2.5% discount</a>

For rich text elements used throughout your application, you may provide a map
of tag names to rich text formatting functions, when configuring FormatPHP.

```php
$config = new Config(
    locale: new Intl\Locale('en-US'),
    defaultRichTextElements: [
        'em' => fn ($text) => "<em class=\"myClass\">$text</em>",
        'strong' => fn ($text) => "<strong class=\"myClass\">$text</strong>",
    ],
);
```

Using this approach, consider the following formatted message:

```php
$formatphp->formatMessage([
    'id' => 'welcome',
    'defaultMessage' => 'Welcome, <strong><em>{name}</em></strong>',
], [
    'name' => 'Sam',
]);
```

It will produce a string similar to the following:

    Welcome, <strong class="myClass"><em class="myClass">Sam</em></strong>

### Using MessageLoader to Load Messages

We also provide a message loader to load translation strings from locale files
that have been generated by your translation management system.

```php
use FormatPHP\MessageLoader;

$messageLoader = new MessageLoader(
    // The path to your locale JSON files (i.e., en.json, es.json, etc.).
    messagesDirectory: '/path/to/app/locales',
    // The configuration object created earlier.
    config: $config,
);

$messagesInSpanish = $messageLoader->loadMessages();
```

This example assumes a directory of locale JSON files located at
`/path/to/app/locales`, which includes an `en.json` file with these
contents:

```json
{
  "hello": {
    "defaultMessage": "Hello, {name}! Today is {ts, date, ::yyyyMMdd}."
  }
}
```

and an `es.json` file with these contents:

```json
{
  "hello": {
    "defaultMessage": "¡Hola {name}! Hoy es {ts, date, ::yyyyMMdd}."
  }
}
```

The message loader uses a fallback method to choose locales. In this example,
if the user's locale is `es-419` (i.e., Spanish appropriate for the Latin
America and Caribbean region), the fallback method will choose `es.json` for the
messages.

### Using the Console Command To Extract Messages

The `formatphp extract` console command helps you extract messages from your
application source code, saving them to JSON files that your translation
management system can use.

```shell
./vendor/bin/formatphp extract \
    --out-file=locales/en.json \
    'src/**/*.php' \
    'src/**/*.phtml'
```

In order for message extraction to function properly, we have a few rules that
must be followed (see comments inline in the following example):

```php
// The method name must be exactly `formatMessage`. (see note below)
// The name of the variable does not matter.
$formatphp->formatMessage(
    // The message descriptor should be an array literal.
    [
        'id' => 'hello', // ID (if provided) should be a string literal.
        'description' => 'Message to translators', // Description should be a string literal.
        'defaultMessage' => 'My name is {name}', // Message should be a string literal.
    ],
    [
        'name' => $userName,
    ],
);
```

At least one of `id` or `defaultMessage` must be present.

> ℹ️ If you wish to use a different function name (e.g., maybe you wish to wrap
> this method call in a Closure, etc.), you may do so, but you must provide the
> `--additional-function-names` option to the `formatphp extract` console
> command. This option takes a comma-separated list of function names for the
> extractor to parse.
>
> ```
> --additional-function-names='formatMessage, myCustomFormattingFunction'
> ```
>
> To see all available options, view the command help with `formatphp help extract`.

### Pseudo Locales

Pseudo locales provide a way to test an application with various types of
characters and string widths. FormatPHP provides a tool to convert any locale
file to a pseudo locale for testing purposes.

Given the English message `my name is {name}`, the following table shows how
each supported pseudo locale will render this message.

| Locale  | Message                                      |
|---------|----------------------------------------------|
| `en-XA` | `ṁẏ ńâṁè íś {name}`                          |
| `en-XB` | `[!! ṁẏ ńâṁṁṁè íííś  !!]{name}`              |
| `xx-AC` | `MY NAME IS {name}`                          |
| `xx-HA` | `[javascript]my name is {name}`              |
| `xx-LS` | `my name is {name}SSSSSSSSSSSSSSSSSSSSSSSSS` |

To convert a locale to a pseudo locale, use the `formatphp pseudo-locale` command.

```shell
./vendor/bin/formatphp pseudo-locale \
    --out-file locales/en-XA.json \
    locales/en.json \
    en-XA
```

> ℹ️ To see all available options, view the command help with
> `formatphp help pseudo-locale`.

## TMS Support

A [translation management system](https://en.wikipedia.org/wiki/Translation_management_system),
or TMS, allows translators to use your default locale file to create translations
for all the other languages your application supports. To work with a TMS, you
will extract the formatted strings from your application to send to the TMS.
Often, a TMS will specify a particular document format they require.

Out of the box, FormatPHP supports the following formatters for integration with
third-party TMSes. Supporting a TMS does not imply endorsement of that
particular TMS.

| TMS                                                                                  | `--format`  |
|--------------------------------------------------------------------------------------|-------------|
| [Crowdin Chrome JSON](https://support.crowdin.com/file-formats/chrome-json/)         | `crowdin`   |
| [Lingohub](https://lingohub.com/developers/resource-files/json-localization/)        | `simple`    |
| [locize](https://docs.locize.com/integration/supported-formats#json-flatten)         | `simple`    |
| [Phrase](https://help.phrase.com/help/simple-json)                                   | `simple`    |
| [SimpleLocalize](https://simplelocalize.io/docs/integrations/format-js-cli/)         | `simple`    |
| [Smartling ICU JSON](https://help.smartling.com/hc/en-us/articles/360008000733-JSON) | `smartling` |

Our default formatter is `formatphp`, which mirrors the output of default
formatter for FormatJS.

### Custom Formatters

You may provide your own formatter using our interfaces. You will need to
create a writer for the format. Optionally, you may create a reader, if using
our message loader or the `formatphp pseudo-locale` command with the
`--in-format` option.

* The writer must implement `FormatPHP\Format\WriterInterface` or be a callable
  of the shape `callable(FormatPHP\DescriptorCollection, FormatPHP\Format\WriterOptions): mixed[]`.
* The reader must implement `FormatPHP\Format\ReaderInterface` or be a
  callable of the shape `callable(mixed[]): FormatPHP\MessageCollection`.

To use your custom writer with `formatphp extract`, pass the fully-qualified
class name to `--format`, or a path to a script that returns the callable.

For example, given the script `my-writer.php` with the contents:

```php
<?php

use FormatPHP\DescriptorCollection;
use FormatPHP\Format\WriterOptions;

require_once 'vendor/autoload.php';

/**
 * @return mixed[]
 */
return function(DescriptorCollection $descriptors, WriterOptions $options): array {
    // Custom writer logic to create an array of data we will write
    // as JSON to a file, which your TMS will be able to use.
};
```

You can call `formatphp extract` like this:

```shell
./vendor/bin/formatphp extract \
    --format='path/to/my-writer.php' \
    --out-file=locales/en.json \
    'src/**/*.php'
```

To use a custom reader with the message loader:

```php
$messageLoader = new MessageLoader(
    // The path to your locale JSON files (i.e., en.json, es.json, etc.).
    messagesDirectory: '/path/to/app/locales',
    // The configuration object created earlier.
    config: $config,
    // Pass your custom reader through the formatReader parameter.
    formatReader: MyCustomReader::class,
);
```

The `formatReader` parameter accepts the following:

* Fully-qualified class name for a class that implements `FormatPHP\Format\ReaderInterface`
* An already-instantiated instance object of `FormatPHP\Format\ReaderInterface`
* A callable with the shape `callable(mixed[]): FormatPHP\MessageCollection`
* The path to a script that returns a callable with this shape

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
MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
