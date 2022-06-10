.. _reference.intl-datetimeformatoptions:

===========================
Intl\\DateTimeFormatOptions
===========================

You can fine-tune date and time formatting with ``DateTimeFormatOptions``.

.. code-block:: php

    use FormatPHP\Config;
    use FormatPHP\FormatPHP;
    use FormatPHP\Intl;

    $config = new Config(new Intl\Locale('es-ES'));
    $formatphp = new FormatPHP($config);

    $date = new DateTimeImmutable('now');

    echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
        'dateStyle' => 'medium',
    ])); // e.g., "10 jun 2022"

    echo $formatphp->formatTime($date, new Intl\DateTimeFormatOptions([
        'timeStyle' => 'long',
    ])); // e.g., "0:58:05 UTC"

General Formatting
##################

``DateTimeFormatOptions`` accepts the following general options to format dates
and times:

dateStyle
    General formatting of the date, according to the locale. Possible values
    are: *full*, *long*, *medium*, and *short*.

    .. code-block:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'dateStyle' => 'full',
        ])); // e.g., "viernes, 10 de junio de 2022"

timeStyle
    General formatting of the time, according to the locale. Possible values
    are: *full*, *long*, *medium*, and *short*.

    .. code-block:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'timeStyle' => 'full',
        ])); // e.g., "1:10:33 (tiempo universal coordinado)"

.. tip::
    You may use both ``dateStyle`` and ``timeStyle`` together, but if used with
    any of the specific formatting options (i.e., ``weekday``, ``hour``,
    ``month``, etc.), FormatPHP will throw
    ``FormatPHP\Exception\InvalidArgumentException``.

Specific Formatting
###################

In addition to the general formatting options, the following options provide
more specific control over date and time formatting.

.. code:: php

    echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
        'era' => 'short',
        'year' => '2-digit',
        'month' => 'short',
        'weekday' => 'short',
        'day' => 'numeric',
        'hour' => '2-digit',
        'minute' => '2-digit',
        'second' => '2-digit',
    ])); // e.g., "vie, 10 jun 22 d. C., 1:24:01"

era
    The locale representation of the era (e.g. "AD", "BC"). Possible values are:
    *long*, *short*, and *narrow*.

year
    The locale representation of the year. Possible values are: *numeric* or
    *2-digit*.

month
    The locale representation of the month. Possible values are: *numeric*,
    *2-digit*, *long*, *short*, or *narrow*.

weekday
    The locale representation of the weekday name. Possible values are: *long*,
    *short*, and *narrow*.

day
    The locale representation of the day. Possible values are: *numeric* or
    *2-digit*.

hour
    The locale representation of the hour. Possible values are: *numeric* or
    *2-digit*.

minute
    The locale representation of the minute. Possible values are: *numeric* or
    *2-digit*.

second
    The locale representation of the seconds. Possible values are: *numeric* or
    *2-digit*.

.. hint::
    Not all locales support the same formatting. For example, some locales treat
    *short* and *narrow* eras with the same presentation. Others may treat
    *numeric* and *2-digit* hours with the same presentation.

    These formats are hints for how to display dates and times, according to the
    given locale. When localizing content, use the locale's preferred
    formatting. This is what the underlying ICU library does, and therefore,
    this what FormatPHP does.

Additional Options
##################

You may use any of the following additional options to further influence date
and time formatting.

.. hint::
    While you may use these options with ``dateStyle`` and ``timeStyle``, the
    ``dateStyle`` and ``timeStyle`` general formatting options rigidly stick to
    the preferences of the locale, so some of these options might not appear to
    have any effect. For example, setting ``hourCycle`` to *h23* will not have
    any effect when used with ``timeStyle`` in the en-US locale. This is because
    en-US prefers *h12*. Instead, you may use the specific formatting options
    with these additional options to achieve the desired results.

calendar
    The calendar system to use. Possible values include: *buddhist*, *chinese*,
    *coptic*, *dangi*, *ethioaa*, *ethiopic*, *gregory*, *hebrew*, *indian*,
    *islamic*, *islamic-civil*, *islamic-rgsa*, *islamic-tbla*,
    *islamic-umalqura*, *iso8601*, *japanese*, *persian*, or *roc*.

    .. code:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'dateStyle' => 'full',
            'calendar' => 'japanese',
        ])); // e.g., "Friday, June 10, 4 Reiwa" when locale is en-US

dayPeriod
    The formatting style used for day periods like "in the morning", "am",
    "noon", "n" etc. Keep in mind not all locales may support presentation of
    day periods.

    Possible values are: *narrow*, *short*, or *long*.

    .. code:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'hour' => 'numeric',
            'dayPeriod' => 'long',
        ])); // e.g., "1 at night" when locale is en-US

hour12
    If ``true``, ``hourCycle`` will be *h12*, if ``false``, ``hourCycle`` will
    be *h23*. This property overrides any value set by ``hourCycle``.

    .. code:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'hour' => '2-digit',
            'minute' => '2-digit',
            'hour12' => false,
        ])); // e.g., "13:47"

hourCycle
    The hour cycle to use. Possible values are: *h11*, *h12*, *h23*, and *h24*.

    If specified, this property overrides the ``hc`` property of the locale's
    language tag. The ``hour12`` property takes precedence over this value.

    Not all locales support each of these values.

    .. code:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'hour' => '2-digit',
            'minute' => '2-digit',
            'hourCycle' => 'h12',
        ])); // e.g., "2:06 p. m." when locale is es-ES

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

    .. code:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'hour' => '2-digit',
            'minute' => '2-digit',
            'numberingSystem' => 'jpan',
        ])); // e.g., "十四:十三"

timeZoneName
    An indicator for how to format the localized representation of the time zone
    name. Values are: *long*, *short*, *shortOffset*, *longOffset*,
    *shortGeneric*, or *longGeneric*.

    .. code:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'hour' => '2-digit',
            'minute' => '2-digit',
            'timeZoneName' => 'long',
        ])); // e.g., "14:17 協定世界時" when the locale is ja-JP

timeZone
    The time zone to use. The default is the system's default time zone (see
    `date_default_timezone_set()`_). You may use the zone names of the
    `IANA time zone database`_, such as "Asia/Shanghai", "Asia/Kolkata",
    "America/New_York".

    .. code:: php

        echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
            'hour' => '2-digit',
            'minute' => '2-digit',
            'timeZone' => 'America/Chicago',
            'timeZoneName' => 'long',
        ])); // e.g., "9:21 AM Central Daylight Time" when the locale is en-US

.. _numbering system: https://cldr.unicode.org/translation/core-data/numbering-systems
.. _numbering system defined within Unicode CLDR: https://github.com/unicode-org/cldr/blob/main/common/bcp47/number.xml
.. _date_default_timezone_set(): https://www.php.net/date_default_timezone_set
.. _IANA time zone database: https://www.iana.org/time-zones
