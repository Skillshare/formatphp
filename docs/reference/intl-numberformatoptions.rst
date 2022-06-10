.. _reference.intl-numberformatoptions:

=========================
Intl\\NumberFormatOptions
=========================

You can fine-tune number and currency formatting with ``NumberFormatOptions``.

.. code-block:: php

    use FormatPHP\Config;
    use FormatPHP\FormatPHP;
    use FormatPHP\Intl;

    $config = new Config(new Intl\Locale('es-ES'));
    $formatphp = new FormatPHP($config);

    $number = -12_345.678;

    echo $formatphp->formatNumber($number, new Intl\NumberFormatOptions([
        'style' => 'unit',
        'unit' => 'meter',
        'unitDisplay' => 'long',
    ])); // e.g., "-12.345,678 metros"

    echo $formatphp->formatCurrency($number, 'USD', new Intl\NumberFormatOptions([
        'currencySign' => 'accounting',
        'currencyDisplay' => 'symbol',
    ])); // e.g., "-12.345,68 US$"

Notation & Style
################

``NumberFormatOptions`` accepts the following options to specify the style and
type of notation desired:

notation
    The type of number formatting to use. Possible values are: *standard*,
    *scientific*, *engineering*, and *compact*. The default is
    *standard*.

    .. code-block:: php

        echo $formatphp->formatNumber(1234.5678, new Intl\NumberFormatOptions([
            'notation' => 'scientific',
        ])); // e.g., "1.235E3"

        echo $formatphp->formatNumber(1234.5678, new Intl\NumberFormatOptions([
            'notation' => 'compact',
        ])); // e.g., "1.2K"

style
    The style of the number formatting. Possible values are: *decimal*,
    *currency*, *percent*, and *unit*.

    The default is *decimal* when using ``formatNumber()``. When using
    ``formatCurrency()``, this value is always *currency* no matter
    what value is set on the ``NumberFormatOptions`` instance.

    .. code-block:: php

        echo $formatphp->formatNumber(0.25, new Intl\NumberFormatOptions([
            'style' => 'percent',
        ])); // e.g., "25%"

        echo $formatphp->formatNumber(42, new Intl\NumberFormatOptions([
            'style' => 'unit',
            'unit' => 'mile',
        ])); // e.g., "42 mi"

General Options
###############

All notation types support the following options to provide more granular
control over number formatting:

signDisplay
    Controls when to display the sign symbol for the number. The default is *auto*.

    Possible values are:

    * *always*: Always display the sign.
    * *auto*: Use the locale to determine when to display the sign.
    * *exceptZero*: Display the sign for positive and negative numbers, but
      never display the sign for zero.
    * *never*: Never display the sign.

    .. code-block:: php

        echo $formatphp->formatNumber(-123, new Intl\NumberFormatOptions([
            'signDisplay' => 'never',
        ])); // e.g., "123"

roundingMode
    Controls the rounding rules for the number. The default is *halfEven*.

    Possible values are:

    * *ceil*: All values are rounded towards positive infinity (+∞).
    * *floor*: All values are rounded towards negative infinity (-∞).
    * *expand*: All values are rounded away from zero.
    * *trunc*: All values are rounded towards zero.
    * *halfCeil*: Values exactly on the 0.5 (half) mark are rounded towards
      positive infinity (+∞).
    * *halfFloor*: Values exactly on the 0.5 (half) mark are rounded towards
      negative infinity (-∞).
    * *halfExpand*: Values exactly on the 0.5 (half) mark are rounded away from zero.
    * *halfTrunc*: Values exactly on the 0.5 (half) mark are rounded towards zero.
    * *halfEven*: Values exactly on the 0.5 (half) mark are rounded to the nearest
      even digit. This is often called Banker’s Rounding because it is, on average,
      free of bias.
    * *halfOdd*: Similar to *halfEven*, but rounds ties to the nearest odd number
      instead of even number.
    * *unnecessary*: This mode doesn't perform any rounding but will throw an
      exception if the value cannot be represented exactly without rounding.

    .. code-block:: php

        echo $formatphp->formatNumber(2.0000335, new Intl\NumberFormatOptions([
            'roundingMode' => 'halfEven',
        ])); // e.g., "2.000034"

        echo $formatphp->formatNumber(2.0000335, new Intl\NumberFormatOptions([
            'roundingMode' => 'halfOdd',
        ])); // e.g., "2.000033"

useGrouping
    Controls display of grouping separators, such as thousand separators or
    thousand/lakh/crore separators. The default is *auto*.

    Possible values are:

    * *always*: Always display grouping separators, even if the locale prefers otherwise.
    * *auto*: Use the locale's preference for grouping separators.
    * *false*: Do not display grouping separators. Please note this is a string
      value and not a boolean ``false`` value.
    * *min2*: Display grouping separators when there are at least two digits in a group.
    * *true*: This is an alias for *always*. Please note this is a string value
      and not a boolean ``true`` value.

    .. code-block:: php

        echo $formatphp->formatNumber(1234, new Intl\NumberFormatOptions([
            'useGrouping' => 'min2',
        ])); // e.g., "1234"

        echo $formatphp->formatNumber(12345, new Intl\NumberFormatOptions([
            'useGrouping' => 'min2',
        ])); // e.g., "12,345"

scale
    A scale by which to multiply the number before formatting it. For
    example, a value of 100 will multiply the number by 100 first, then
    apply other formatting options.

    .. code-block:: php

        echo $formatphp->formatNumber(0.042, new Intl\NumberFormatOptions([
            'scale' => 1000,
        ])); // e.g., "42"

minimumIntegerDigits
    Specifies the minimum number of integer digits to use. The default is 1.

    .. code-block:: php

        echo $formatphp->formatNumber(5, new Intl\NumberFormatOptions([
            'minimumIntegerDigits' => 3,
        ])); // e.g., "005"

maximumFractionDigits
    Specifies the maximum number of fraction digits to use.

    .. code-block:: php

        echo $formatphp->formatNumber(1.23456, new Intl\NumberFormatOptions([
            'maximumFractionDigits' => 3,
        ])); // e.g., "1.235"

minimumFractionDigits
    Specifies the minimum number of fraction digits to use.

    ``minimumFractionDigits`` cannot be greater than ``maximumFractionDigits``.

    .. code-block:: php

        echo $formatphp->formatNumber(42.1, new Intl\NumberFormatOptions([
            'minimumFractionDigits' => 2,
        ])); // e.g., "42.10"

maximumSignificantDigits
    Specifies the maximum number of significant digits to use.

    .. code-block:: php

        echo $formatphp->formatNumber(12345.6789, new Intl\NumberFormatOptions([
            'maximumSignificantDigits' => 3,
        ])); // e.g., "12,300"

minimumSignificantDigits
    Specifies the minimum number of significant digits to use.

    ``minimumSignificantDigits`` cannot be greater than ``maximumSignificantDigits``.

    .. code-block:: php

        echo $formatphp->formatNumber(123.45, new Intl\NumberFormatOptions([
            'minimumSignificantDigits' => 10,
        ])); // e.g., "123.4500000"

numberingSystem
    Specifies a `numbering system`_ to use when representing numeric values. You
    may specify any `numbering system defined within Unicode CLDR`_ and bundled
    in the ICU library version that is available on your platform. However,
    numbering systems featuring algorithmic numbers do not yet work.

    Possible values include (but are not limited to): *adlm*, *ahom*, *arab*,
    *arabext*, *bali*, *beng*, *bhks*, *brah*, *cakm*, *cham*, *deva*, *fullwide*,
    *gong*, *gonm*, *gujr*, *guru*, *hanidec*, *hmng*, *java*, *kali*, *khmr*,
    *knda*, *lana*, *lanatham*, *laoo*, *latn*, *lepc*, *limb*, *mathbold*,
    *mathdbl*, *mathmono*, *mathsanb*, *mathsans*, *mlym*, *modi*, *mong*, *mroo*,
    *mtei*, *mymr*, *mymrshan*, *mymrtlng*, *newa*, *nkoo*, *olck*, *orya*, *osma*,
    *rohg*, *saur*, *shrd*, *sind*, *sora*, *sund*, *takr*, *talu*, *tamldec*,
    *telu*, *thai*, *tibt*, *tirh*, *vaii*, *wara*, and *wcho*.

    .. code-block:: php

        echo $formatphp->formatNumber(123.456, new Intl\NumberFormatOptions([
            'numberingSystem' => 'tibt',
        ])); // e.g., "༡༢༣.༤༥༦"

Fraction Options
################

The following options affect the formatting of fractional digits (e.g., when
using ``minimumFractionDigits`` or ``maximumFractionDigits``).

trailingZeroDisplay
    Controls the display of trailing zeros when formatting numbers. The default
    is *auto*.

    * *auto*: Keep the trailing zeros according to the rules defined in
      ``minimumFractionDigits`` and ``maximumFractionDigits``.
    * *stripIfInteger*: If the formatted number is a whole integer, do not display
      trailing zeros.

    .. code-block:: php

        echo $formatphp->formatNumber(42.001, new Intl\NumberFormatOptions([
            'maximumFractionDigits' => 2,
            'trailingZeroDisplay' => 'stripIfInteger',
        ])); // e.g., "42"

roundingPriority
    Specifies how to resolve conflicts between maximum fraction digits and
    maximum significant digits. The default is *auto*.

    * *auto*: The significant digits always win a conflict.
    * *morePrecision*: The result with more precision wins the conflict.
    * *lessPrecision*: The result with less precision wins the conflict.

    .. code-block:: php

        echo $formatphp->formatNumber(123.4567, new Intl\NumberFormatOptions([
            'maximumSignificantDigits' => 6,
            'maximumFractionDigits' => 2,
            'roundingPriority' => 'morePrecision',
        ])); // e.g., "123.457"

        echo $formatphp->formatNumber(123.4567, new Intl\NumberFormatOptions([
            'maximumSignificantDigits' => 6,
            'maximumFractionDigits' => 2,
            'roundingPriority' => 'lessPrecision',
        ])); // e.g., "123.46"

Currency Options
################

When formatting currency, you may use the following options.

currency
    An `ISO 4217 currency code`_ to use when formatting currency. For example,
    *USD*, *EUR*, or *CNY*.

    This option is required if the ``style`` option is *currency*.

    .. code-block:: php

        echo $formatphp->formatNumber(123.456, new Intl\NumberFormatOptions([
            'style' => 'currency',
            'currency' => 'EUR',
        ])); // e.g., "€123.46"

currencySign
    In accounting, many locales format negative currency values using
    parentheses rather than the minus sign. You may enable this behavior by
    setting this property to *accounting*. The default value is *standard*.

    .. code-block:: php

        echo $formatphp->formatNumber(-56.359, new Intl\NumberFormatOptions([
            'style' => 'currency',
            'currency' => 'USD',
            'currencySign' => 'accounting',
        ])); // e.g., "($56.36)"

currencyDisplay
    How to display the currency.

    * *symbol*: Use a localized currency symbol when formatting the currency. This
      is the default.
    * *narrowSymbol*: Use a narrow format for the currency symbol. For example, in
      some locales (e.g., en-GB), USD currency will default to display as "US$100."
      When using *narrowSymbol*, it might display as "$100" instead. This behavior
      is locale-dependent.
    * *code*: Use the ISO currency code when formatting currency (e.g., "USD 100").
    * *name*: Use a localized name for the currency (e.g., "100 US dollars").

    .. code-block:: php

        echo $formatphp->formatNumber(343.199, new Intl\NumberFormatOptions([
            'style' => 'currency',
            'currency' => 'GBP',
            'currencyDisplay' => 'name',
        ])); // e.g., "343.20 British pounds"

Unit Options
############

When formatting units, you may use the following options.

unit
    If the ``style`` option is *unit*, you must provide a unit identifier as
    the ``unit`` property. `UTS #35, Part 2, Section 6`_ defines the core unit
    identifiers. You may use any unit defined in the `CLDR data file`_.

    You may use the following units in these concise forms (without the prefixes
    defined in CLDR): *acre*, *bit*, *byte*, *celsius*, *centimeter*, *day*, *degree*,
    *fahrenheit*, *fluid-ounce*, *foot*, *gallon*, *gigabit*, *gigabyte*, *gram*,
    *hectare*, *hour*, *inch*, *kilobit*, *kilobyte*, *kilogram*, *kilometer*,
    *liter*, *megabit*, *megabyte*, *meter*, *mile*, *mile-scandinavian*, *milliliter*,
    *millimeter*, *millisecond*, *minute*, *month*, *ounce*, *percent*, *petabyte*,
    *pound*, *second*, *stone*, *terabit*, *terabyte*, *week*, *yard*, or *year*.

    .. code-block:: php

        // Displaying hours in Japanese.
        $formatphp = new FormatPHP(new Config(new Intl\Locale('ja-JP')));

        echo $formatphp->formatNumber(14, new Intl\NumberFormatOptions([
            'style' => 'unit',
            'unit' => 'hour',
            'unitDisplay' => 'long',
        ])); // e.g., "14 時間"

        // Displaying fluid ounces in French.
        $formatphp = new FormatPHP(new Config(new Intl\Locale('fr-FR')));

        echo $formatphp->formatNumber(64, new Intl\NumberFormatOptions([
            'style' => 'unit',
            'unit' => 'fluid-ounce',
            'unitDisplay' => 'long',
        ])); // e.g., "64 onces liquides"

unitDisplay
    How to display the unit. Possible values are *short*, *long*, and *narrow*.
    The default is *short*.

    .. code-block:: php

        echo $formatphp->formatNumber(14, new Intl\NumberFormatOptions([
            'style' => 'unit',
            'unit' => 'hour',
            'unitDisplay' => 'narrow',
        ])); // e.g., "14h"

Compact Options
###############

If ``notation`` is *compact*, then you may specify the ``compactDisplay`` property
with the value *short* or *long*. The default is *short*.

    .. code-block:: php

        $formatphp = new FormatPHP(new Config(new Intl\Locale('nl-NL')));

        echo $formatphp->formatNumber(123456.789, new Intl\NumberFormatOptions([
            'notation' => 'compact',
            'compactDisplay' => 'long',
        ])); // e.g., "123 duizend"

        echo $formatphp->formatNumber(123456.789, new Intl\NumberFormatOptions([
            'notation' => 'compact',
            'compactDisplay' => 'short',
        ])); // e.g., "123K"

.. _numbering system: https://cldr.unicode.org/translation/core-data/numbering-systems
.. _numbering system defined within Unicode CLDR: https://github.com/unicode-org/cldr/blob/main/common/bcp47/number.xml
.. _ISO 4217 currency code: https://www.iso.org/iso-4217-currency-codes.html
.. _UTS #35, Part 2, Section 6: https://unicode.org/reports/tr35/tr35-general.html#Unit_Elements
.. _CLDR data file: https://github.com/unicode-org/cldr/blob/main/common/validity/unit.xml
