.. _formatting.dates:

========================
Formatting Dates & Times
========================

You may use the methods ``formatDate()`` and ``formatTime()`` to format
dates and times according to the locale.

.. code-block:: php

    use FormatPHP\Config;
    use FormatPHP\FormatPHP;
    use FormatPHP\Intl;

    $config = new Config(new Intl\Locale('es-ES'));
    $formatphp = new FormatPHP($config);

    $date = new DateTimeImmutable('now');

    echo $formatphp->formatDate($date); // e.g., "10/6/22"
    echo $formatphp->formatTime($date); // e.g., "19:06"

.. tip::
    See the :ref:`reference.intl-datetimeformatoptions` reference for more
    information on the options available.

Formatting Dates
################

The ``formatDate()`` method facilitates localization of dates in your
applications. Its method signature is:

.. code:: php

    public function FormatPHP::formatDate(
        DateTimeInterface | int | string | null $date,
        ?Intl\DateTimeFormatOptions $options = null
    ) string

The ``$date`` argument is the value you wish to localize. It uses the
configured locale's preferred formatting to localize the date. You may provide
a `DateTimeInterface`_ instance, a `Unix timestamp`_, a date string in one of
the `supported date and time formats`_, or ``null`` to use the current date and
time.

To customize the display of the localized date, you may provide a
:ref:`reference.intl-datetimeformatoptions` instance as the ``$options``
argument.

.. code:: php

    $date = new DateTimeImmutable('now');

    echo $formatphp->formatDate($date, new Intl\DateTimeFormatOptions([
        'day' => 'numeric',
        'month' => 'short',
        'weekday' => 'short',
        'year' => 'numeric',
    ])); // e.g., "vie, 10 jun 2022"

Formatting Times
################

The ``formatTime()`` method facilitates localization of times in your
applications. It differs from ``formatDate()`` by using *numeric* as the default
value for the ``hour`` and ``minute`` options. Otherwise, it functions identical
to ``formatDate()``.

Its method signature is:

.. code:: php

    public function FormatPHP::formatTime(
        DateTimeInterface | int | string | null $date,
        ?Intl\DateTimeFormatOptions $options = null
    ) string

The ``$date`` argument is the value you wish to localize. It uses the
configured locale's preferred formatting to localize the time. You may provide
a `DateTimeInterface`_ instance, a `Unix timestamp`_, a date string in one of
the `supported date and time formats`_, or ``null`` to use the current date and
time.

To customize the display of the localized time, you may provide a
:ref:`reference.intl-datetimeformatoptions` instance as the ``$options``
argument.

.. code:: php

    $date = new DateTimeImmutable('now');

    echo $formatphp->formatTime($date, new Intl\DateTimeFormatOptions([
        'timeStyle' => 'full',
        'timeZone' => 'America/Chicago',
    ])); // e.g., "14:21:50 (hora de verano central)"

.. _DateTimeInterface: https://www.php.net/datetimeinterface
.. _Unix timestamp: https://en.wikipedia.org/wiki/Unix_time
.. _supported date and time formats: https://www.php.net/manual/en/datetime.formats.php
