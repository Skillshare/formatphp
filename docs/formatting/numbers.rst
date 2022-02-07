.. _formatting.numbers:

=============================
Formatting Numbers & Currency
=============================

You may use the methods ``formatNumber()`` and ``formatCurrency()`` to format
numbers and currency according to the locale.

.. code-block:: php

    use FormatPHP\Config;
    use FormatPHP\FormatPHP;
    use FormatPHP\Intl;

    $config = new Config(new Intl\Locale('es-ES'));
    $formatphp = new FormatPHP($config);

    $number = -12_345.678;

    echo $formatphp->formatNumber($number);          // e.g., "-12.345,678"
    echo $formatphp->formatCurrency($number, 'USD'); // e.g., "-12.345,68 $"

.. tip::
    See the :ref:`reference.intl-numberformatoptions` reference for more
    information on the options available.

Formatting Numbers
##################

The ``formatNumber()`` method facilitates localization of numbers in your
applications. Its method signature is:

.. code:: php

    public function FormatPHP::formatNumber(
        float | int $number,
        ?Intl\NumberFormatOptions $options = null
    ) string

The ``$number`` argument is the value you wish to localize. It uses the
configured locale's preferred formatting to localize the number.

To customize the display of the localized number, you may provide a
:ref:`reference.intl-numberformatoptions` instance as the ``$options``
argument.

.. code-block::
    :caption: Distance to Proxima Centauri in kilometers.

    echo $formatphp->formatNumber(4.2465, new Intl\NumberFormatOptions([
        'notation' => 'scientific',
        'style' => 'unit',
        'unit' => 'kilometer',
        'scale' => 9.46 * (10 ** 12), // Kilometers in a light year
    ])); // e.g., "4.017E13 km"

Formatting Currency
###################

While you may specify *currency* in the ``style`` property of
``NumberFormatOptions`` when using ``formatNumber()``, you may also use the
convenience method ``formatCurrency()``, whose signature is:

.. code:: php

    public function FormatPHP::formatCurrency(
        float | int $number,
        string $currencyCode,
        ?Intl\NumberFormatOptions $options = null
    ) string

The ``$number`` argument is the currency value you wish to localize. Like
``formatNumber()``, it uses the configured locale's preferred formatting to
localize the currency.

The ``$currencyCode`` argument is an `ISO 4217 currency code`_ to use when
formatting currency. For example, *USD*, *EUR*, or *CNY*.

.. code::

    $config = new Config(new Intl\Locale('es-ES'));
    $formatphp = new FormatPHP($config);

    echo $formatphp->formatCurrency(123.0, 'USD', new Intl\NumberFormatOptions([
        'currencyDisplay' => 'symbol',
        'trailingZeroDisplay' => 'stripIfInteger',
    ])); // e.g., "123 US$"

.. _ISO 4217 currency code: https://www.iso.org/iso-4217-currency-codes.html
