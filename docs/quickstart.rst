.. _quickstart:

===============
Getting Started
===============

FormatPHP helps you internationalize and localize your PHP applications.
Through the use of its API, you can format messages for any locale, including
localization of dates and times, numbers, and pluralization. FormatPHP includes
tools for extracting messages for translation and loading translated messages
to render, based on a locale.

The general steps when working with FormatPHP are:

1. Load translation strings.
2. Configure FormatPHP for a locale.
3. Format strings in your application with ``formatMessage()`` and localize data
   with the other formatter methods (e.g., ``formatNumber()``, ``formatDate()``,
   ``formatTime()``, etc.).

Requirements
############

FormatPHP requires:

* PHP 7.4+
* `ext-intl <https://www.php.net/intl>`_
* `ext-json <https://www.php.net/json>`_
* `ext-mbstring <https://www.php.net/mbstring>`_
* `libicu 69.1 <https://icu.unicode.org>`_ or later

Install With Composer
#####################

The only supported installation method for FormatPHP is
`Composer <https://getcomposer.org>`_. Use the following command to add
FormatPHP to your project dependencies:

.. code-block:: bash

    composer require skillshare/formatphp

Using FormatPHP
###############

The following example shows a complete working implementation of FormatPHP:

.. code-block:: php

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
        'today' => $formatphp->formatDate(new DateTimeImmutable()),
    ]); // e.g., ¡Hola Arwen! Hoy es 31/1/22.
