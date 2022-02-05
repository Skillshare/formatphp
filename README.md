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
    new Message('hello', '¡Hola {name}! Hoy es {today}.'),
]);

$config = new Config(
    // Locale of the application (or of the user using the application).
    new Intl\Locale('es-ES'),
);

$formatphp = new FormatPHP($config, $messagesInSpanish);

echo $formatphp->formatMessage([
    'id' => 'hello',
    'defaultMessage' => 'Hello, {name}! Today is {today}.',
], [
    'name' => 'Arwen',
    'today' => $formatphp->formatDate(time()),
]); // e.g., ¡Hola Arwen! Hoy es 31/1/22.
```

### Formatting Numbers and Currency

You may use the methods `formatNumber()` and `formatCurrency()` for format
numbers and currency, according to the locale.

```php
use FormatPHP\Config;
use FormatPHP\FormatPHP;
use FormatPHP\Intl;

$config = new Config(new Intl\Locale('es-ES'));
$formatphp = new FormatPHP($config);

$number = -12_345.678;

echo $formatphp->formatNumber($number);          // e.g., "-12.345,678"
echo $formatphp->formatCurrency($number, 'USD'); // e.g., "-12.345,68 $"
```

#### Using Intl\NumberFormatOptions with formatNumber() and formatCurrency()

Fine-tune number and currency formatting with `Intl\NumberFormatOptions`.

```php
echo $formatphp->formatNumber($number, new Intl\NumberFormatOptions([
    'style' => 'unit',
    'unit' => 'meter',
    'unitDisplay' => 'long',
])); // e.g., "-12.345,678 metros"

echo $formatphp->formatCurrency($number, 'USD', new Intl\NumberFormatOptions([
    'currencySign' => 'accounting',
    'currencyDisplay' => 'long',
])); // e.g., "-12.345,68 US$"
 ```

`NumberFormatOptions` accepts the following options to specify the style and
type of notation desired:

* `notation`: The number formatting to use. Possible values include: `standard`,
  `scientific`, `engineering`, and `compact`. The default is `standard`.
* `style`: The number formatting style to use. Possible values include:
  `decimal`, `currency`, `percent`, and `unit`. The default is `decimal` when
  using `formatNumber()`. When using `formatCurrency()`, this value will always
  be `currency` no matter what value is set on the `NumberFormatOptions` instance.

All notations support the following properties to provide more granular control
over the formatting of numbers:

* `signDisplay`: Controls when to display the sign for the number. Defaults to
  `auto`. Possible values include:
  * `always`: Always display the sign.
  * `auto`: Use the locale to determine when to display the sign.
  * `exceptZero`: Display the sign for positive and negative numbers, but never
    display the size for zero.
  * `never`: Never display the sign.
* `roundingMode`: Controls rounding rules for the number. The default is
  `halfEven`. Possible values include:
  * `ceil`: All values are rounded towards positive infinity (+∞).
  * `floor`: All values are rounded towards negative infinity (-∞).
  * `expand`: All values are rounded away from zero.
  * `trunc`: All values are rounded towards zero.
  * `halfCeil`: Values exactly on the 0.5 (half) mark are rounded towards
    positive infinity (+∞).
  * `halfFloor`: Values exactly on the 0.5 (half) mark are rounded towards
    negative infinity (-∞).
  * `halfExpand`: Values exactly on the 0.5 (half) mark are rounded away from zero.
  * `halfTrunc`: Values exactly on the 0.5 (half) mark are rounded towards zero.
  * `halfEven`: Values exactly on the 0.5 (half) mark are rounded to the nearest
    even digit. This is often called Banker’s Rounding because it is, on average,
    free of bias.
  * `halfOdd`: Similar to `halfEven`, but rounds ties to the nearest odd number
    instead of even number.
  * `unnecessary`: This mode doesn't perform any rounding but will throw an
    exception if the value cannot be represented exactly without rounding.
* `useGrouping`: Controls display of grouping separators, such as thousand
  separators or thousand/lakh/crore separators. The default is `auto`. Possible
  values include:
  * `always`: Always display grouping separators, even if the locale prefers otherwise.
  * `auto`: Use the locale's preference for grouping separators.
  * `false`: Do not display grouping separators. Please note this is a string
    value and not a boolean `false` value.
  * `min2`: Display grouping separators when there are at least two digits in a group.
  * `true`: This is an alias for `always`. Please note this is a string value
    and not a boolean `true` value.
* `scale`: A scale by which to multiply the number before formatting it. For
  example, a scale value of 100 will multiply the number by 100 first, then
  apply other formatting options.
* `minimumIntegerDigits`: Specifies the minimum number of integer digits to use.
  The default is 1.
* `minimumFractionDigits` and `maximumFractionDigits`: Specifies the minimum and
  maximum number of fraction digits to use.
* `minimumSignificantDigits` and `maximumSignificantDigits`: Specifies the
  minimum and maximum number of significant digits to use.
* `numberingSystem`: Specifies a [numbering system](https://cldr.unicode.org/translation/core-data/numbering-systems)
  to use when representing numeric values. You may specify any [numbering system
  defined within Unicode CLDR](https://github.com/unicode-org/cldr/blob/main/common/bcp47/number.xml)
  and bundled in the ICU library version that is available on your platform.
  However, numbering systems featuring algorithmic numbers do not yet work.
  Possible values include (but are not limited to): `adlm`, `ahom`, `arab`,
  `arabext`, `bali`, `beng`, `bhks`, `brah`, `cakm`, `cham`, `deva`, `fullwide`,
  `gong`, `gonm`, `gujr`, `guru`, `hanidec`, `hmng`, `java`, `kali`, `khmr`,
  `knda`, `lana`, `lanatham`, `laoo`, `latn`, `lepc`, `limb`, `mathbold`,
  `mathdbl`, `mathmono`, `mathsanb`, `mathsans`, `mlym`, `modi`, `mong`, `mroo`,
  `mtei`, `mymr`, `mymrshan`, `mymrtlng`, `newa`, `nkoo`, `olck`, `orya`, `osma`,
 `rohg`, `saur`, `shrd`, `sind`, `sora`, `sund`, `takr`, `talu`, `tamldec`,
  `telu`, `thai`, `tibt`, `tirh`, `vaii`, `wara`, and `wcho`.

#### Formatting Fractions

The following properties affect the formatting of fractional digits (e.g., when
using `minimumFractionDigits` or `maximumFractionDigits`).

* `trailingZeroDisplay`: Controls the display of trailing zeros when formatting
  numbers. The default is `auto`.
  * `auto`: Keep the trailing zeros according to the rules defined in
    `minimumFractionDigits` and `maximumFractionDigits`.
  * `stripIfInteger`: If the formatted number is a whole integer, do not display
    trailing zeros.
* `roundingPriority`: Specifies how to resolve conflicts between maximum fraction
  digits and maximum significant digits. The default is `auto`.
  * `auto`: The significant digits always win a conflict.
  * `morePrecision`: The result with more precision wins the conflict.
  * `lessPrecision`: The result with less precision wins the conflict.

#### Formatting Currency

When formatting currency, you may use the following properties.

* `currencySign`: In accounting, many locales format negative currency values
  using parentheses rather than the minus sign. You may enable this behavior by
  setting this property to `accounting`. The default value is `standard`.
* `currencyDisplay`: How to display the currency. Possible values include:
  * `symbol`: Use a localized currency symbol when formatting the currency. This
    is the default.
  * `narrowSymbol`: Use a narrow format for the currency symbol. For example, in
    some locales (e.g., en-GB), USD currency will default to display as "US$100."
    When using `narrowSymbol`, it will display as "$100."
  * `code`: Use the ISO currency code when formatting currency (e.g., "USD 100").
  * `name`: Use a localized name for the currency (e.g., "100 US dollars").

#### Compact Notation

If `notation` is `compact`, then you may specify the `compactDisplay` property
with the value `short` or `long`. The default is `short`.

### Formatting Dates and Times

You may use the methods `formatDate()` and `formatTime()` to format dates and
times.

```php
use FormatPHP\Config;
use FormatPHP\FormatPHP;
use FormatPHP\Intl;

$config = new Config(new Intl\Locale('es-ES'));
$formatphp = new FormatPHP($config);

$date = new DateTimeImmutable();

echo $formatphp->formatDate($date); // e.g., "31/1/22"
echo $formatphp->formatTime($date); // e.g., "16:58"
```

#### Using Intl\DateTimeFormatOptions with formatDate() and formatTime()

Fine-tune date and time formatting with `Intl\DateTimeFormatOptions`.

```php
echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
    'dateStyle' => 'medium',
])); // e.g., "31 ene 2022"

echo $formatphp->formatTime($date, new Intl\DateTimeFormatOptions([
    'timeStyle' => 'long',
])); // e.g., "16:58:31 UTC"
```

`DateTimeFormatOptions` accepts the following general options for formatting
dates and times:

* `dateStyle`: General formatting of the date, according to the locale. Possible
  values include: `full`, `long`, `medium`, and `short`.
* `timeStyle`: General formatting of the time, according to the locale. Possible
  values include: `full`, `long`, `medium`, and `short`.

> ℹ️ **Note:** `dateStyle` and `timeStyle` may be used together, but they cannot
> be used with more granular formatting options (i.e., `era`, `year`, `month`,
> `day`, `weekday`, `day`, `hour`, `minute`, or `second`).

Instead of `dateStyle` and `timeStyle`, you may use the following options for
more granular formatting of dates and times:

* `era`: The locale representation of the era (e.g. "AD", "BC"). Possible values
  are: `long`, `short`, and `narrow`.
* `year`: The locale representation of the year. Possible values are: `numeric`
  or `2-digit`.
* `month`: The locale representation of the month. Possible values are: `numeric`,
  `2-digit`, `long`, `short`, or `narrow`.
* `weekday`: The locale representation of the weekday name. Possible values are:
  `long`, `short`, and `narrow`.
* `day`: The locale representation of the day. Possible values are: `numeric` or
  `2-digit`.
* `hour`: The locale representation of the hour. Possible values are: `numeric`
  or `2-digit`.
* `minute`: The locale representation of the minute. Possible values are:
  `numeric` or `2-digit`.
* `second`: The locale representation of the seconds. Possible values are:
  `numeric` or `2-digit`.

Additional options include:

* `calendar`: The calendar system to use. Possible values include: `buddhist`,
  `chinese`, `coptic`, `dangi`, `ethioaa`, `ethiopic`, `gregory`,
  `hebrew`, `indian`, `islamic`, `islamic-civil`, `islamic-rgsa`, `islamic-tbla`,
  `islamic-umalqura`, `iso8601`, `japanese`, `persian`, or `roc`.
* `dayPeriod`: The formatting style used for day periods like "in the morning",
  "am", "noon", "n" etc. Values include: `narrow`, `short`, or `long`.
* `fractionalSecondDigits`: The number of digits used to represent fractions of
  a second (any additional digits are truncated). Values may be: `0`, `1`, `2`,
  or `3`. **This property is not yet implemented.**
* `hour12`: If `true`, `hourCycle` will be `h12`, if `false`, `hourCycle` will
  be `h23`. This property overrides any value set by `hourCycle`.
* `hourCycle`: The hour cycle to use. Values include: `h11`, `h12`, `h23`, and
  `h24`. If specified, this property overrides the `hc` property of the locale's
  language tag. The `hour12` property takes precedence over this value.
* `numberingSystem`: The numeral system to use. Possible values include: `arab`,
  `arabext`, `armn`, `armnlow`, `bali`, `beng`, `brah`, `cakm`, `cham`, `deva`,
  `diak`, `ethi`, `finance`, `fullwide`, `geor`, `gong`, `gonm`, `grek`, `greklow`,
  `gujr`, `guru`, `hanidays`, `hanidec`, `hans`, `hansfin`, `hant`, `hantfin`,
  `hebr`, `hmnp`, `java`, `jpan`, `jpanfin`, `jpanyear`, `kali`, `khmr`, `knda`,
  `lana`, `lanatham`, `laoo`, `latn`, `lepc`, `limb`, `mlym`, `mong`, `mtei`, `mymr`,
  `mymrshan`, `native`, `nkoo`, `olck`, `orya`, `osma`, `rohg`, `roman`, `romanlow`,
  `saur`, `shrd`, `sora`, `sund`, `takr`, `talu`, `taml`, `tamldec`, `telu`,
  `thai`, `tibt`, `tnsa`, `traditional`, `vaii`, or `wcho`.
* `timeZoneName`: An indicator for how to format the localized representation of
  the time zone name. Values include: `long`, `short`, `shortOffset`, `longOffset`,
  `shortGeneric`, or `longGeneric`.
* `timeZone`: The time zone to use. The default is the system's default time zone
  (see [date_default_timezone_set()](https://www.php.net/date_default_timezone_set)).
  You may use the zone names of the [IANA time zone database](https://www.iana.org/time-zones),
  such as "Asia/Shanghai", "Asia/Kolkata", "America/New_York".

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
echo $formatphp->formatMessage([
    'id' => 'priceMessage',
    'defaultMessage' => <<<'EOD'
        Our price is <boldThis>{price}</boldThis>
        with <link>{discount} discount</link>
        EOD,
], [
    'price' => $formatphp->formatCurrency(29.99, 'USD', new Intl\NumberFormatOptions([
        'maximumFractionDigits' => 0,
    ])),
    'discount' => $formatphp->formatNumber(.025, new Intl\NumberFormatOptions([
        'style' => 'percent',
        'minimumFractionDigits' => 1,
    ])),
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
    new Intl\Locale('en-US'),
    null,
    [
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
    '/path/to/app/locales',
    // The configuration object created earlier.
    $config,
);

$messagesInSpanish = $messageLoader->loadMessages();
```

This example assumes a directory of locale JSON files located at
`/path/to/app/locales`, which includes an `en.json` file with these
contents:

```json
{
  "hello": {
    "defaultMessage": "Hello, {name}! Today is {today}."
  }
}
```

and an `es.json` file with these contents:

```json
{
  "hello": {
    "defaultMessage": "¡Hola {name}! Hoy es {today}."
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

Our default formatter is `formatphp`, which mirrors the output of the default
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
    '/path/to/app/locales',
    // The configuration object created earlier.
    $config,
    // Pass your custom reader through the formatReader parameter.
    MyCustomReader::class,
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
