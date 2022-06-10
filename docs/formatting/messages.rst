.. _formatting.messages:

===================
Formatting Messages
===================

The standard way of working with FormatPHP is:

1. Determine the user's locale (there are a variety of ways to do this, outside
   the scope of this documentation).
2. :ref:`Load translated messages <string-extraction.messageloader>` for the
   locale.
3. Configure FormatPHP with the locale and translated messages.
4. Wrap strings you wish to translate and localize in your application with
   calls to ``formatMessage()``.

.. code-block:: php
    :caption: Using a simple message for translation.

    use FormatPHP\Config;
    use FormatPHP\FormatPHP;
    use FormatPHP\Intl;
    use FormatPHP\MessageLoader;

    // Your custom method to determine the user's locale.
    $userLocale = determineUserLocale();

    $config = new Config(new Intl\Locale($userLocale));
    $messageLoader = new MessageLoader('/path/to/app/locales', $config);
    $formatphp = new FormatPHP($config, $messageLoader->loadMessages());

    echo $formatphp->formatMessage([
        'id' => 'hello',
        'defaultMessage' => 'Hello! Welcome to the website!',
    ]);

formatMessage()
###############

The ``formatMessage()`` method facilitates translation and localization of
string messages in your applications. Its method signature is:

.. code:: php

    public function FormatPHP::formatMessage(array $descriptor, array $values = []) string

The ``$descriptor`` argument is an array with the following properties:

id
    A unique message identifier used to locate translated versions of this
    message in the message collection provided to FormatPHP.

    The ``id`` is required if ``defaultMessage`` is not present.

defaultMessage
    The message to format and translate according to the locale. This may be a
    simple string, or it may have placeholders and other complex arguments.

    The ``defaultMessage`` is required if ``id`` is not present. In case the
    ``id`` is not present, FormatPHP will auto-generate an identifier for the
    message.

description
    An optional description that you may use to provide additional context to
    translators and developers. This is especially useful for translation
    management systems, if using the FormatPHP
    :ref:`extraction tools <string-extraction.extract>`.

The optional ``$values`` argument is an array of key/value pairs, where the key
refers to either a :ref:`named placeholder <messages.placeholders>` in the
message or a :ref:`tag for rich-text formatting <messages.rich-text>`.

.. _messages.placeholders:

Placeholders
############

FormatPHP supports `ICU message syntax`_ for placeholders.

In their simplest form, placeholders are names surrounded by curly braces (i.e.,
``{`` and ``}``). Translators will not modify these names, but they are able to
move them around in the string to a position that makes sense according to the
grammar of a given language and locale.

.. code:: php

    echo $formatphp->formatMessage([
        'id' => 'greeting',
        'defaultMessage' => 'Hello, {personName}!',
    ], [
        'personName' => $user->getName(),
    ])

Pluralization and Complex Arguments
###################################

FormatPHP supports ICU message syntax for `pluralization and complex argument
types`_.

Using the classic example from the ICU documentation, the following shows how to
provide complex argument types to FormatPHP. When translating, translators will
properly translate each sub-message of this structure, leaving the complex
arguments intact.

.. code:: php

    echo $formatphp->formatMessage([
        'id' => 'party',
        'defaultMessage' => <<<'EOD'
            {hostGender, select,
                female {{numGuests, plural, offset:1
                    =0 {{host} does not give a party.}
                    =1 {{host} invites {guest} to her party.}
                    =2 {{host} invites {guest} and one other person to her party.}
                    other {{host} invites {guest} and # other people to her party.}
                }}
                male {{numGuests, plural, offset:1
                    =0 {{host} does not give a party.}
                    =1 {{host} invites {guest} to his party.}
                    =2 {{host} invites {guest} and one other person to his party.}
                    other {{host} invites {guest} and # other people to his party.}
                }}
                other {{numGuests, plural, offset:1
                    =0 {{host} does not give a party.}
                    =1 {{host} invites {guest} to their party.}
                    =2 {{host} invites {guest} and one other person to their party.}
                    other {{host} invites {guest} and # other people to their party.}
                }}
            }
            EOD,
    ], [
        'hostGender' => $host->getGender(),
        'host' => $host->getName(),
        'numGuests' => count($party->guests),
        'guest' => $guest->getName(),
    ]);

Localization
############

FormatPHP supports ICU message syntax for `formatting numbers`_, including
currency and units, as well as `dates and times`_.

.. code:: php

    echo $formatphp->formatMessage([
        'id' => 'hello',
        'defaultMessage' => <<<'EOD'
            On {actionDate, date, ::dMMMM} at {actionDate, time, ::jmm},
            they walked {distance, number, ::unit/kilometer unit-width-full-name .#}
            to pay only {amount, number, ::currency/EUR unit-width-short
            precision-currency-standard/w} in the
            {percentage, number, ::percent precision-integer} off sale
            on furniture.
            EOD,
    ], [
        'actionDate' => new DateTimeImmutable('now'),
        'distance' => 5.358,
        'amount' => 150.00123,
        'percentage' => 0.25,
    ]);

In the en-US locale, this produces a message similar to:

    On June 10 at 6:18 PM, they walked 5.4 kilometers to pay only €150 in the
    25% off sale on furniture.

In a locale for which we have no translations, this message will still have
localization features specific to the locale. For example, in ja-JP, the message
produced is similar to:

    On 6月10日 at 18:21, they walked 5.4 キロメートル to pay only €150 in the 25%
    off sale on furniture.

.. note::
    According to `ECMA-402, section 15.5.4`_ (specifically step 5.b.), if the
    ``style`` is *percent*, then the number formatter must multiply the value by
    100. This means the formatter expects percent values expressed as fractions
    of 100 (i.e., 0.25 for 25%, 0.055 for 5.5%, etc.).

    Since `FormatJS`_ also applies this rule to ``::percent`` number skeletons
    in formatted messages, FormatPHP does, too.

    For example:

    .. code-block:: php

        echo $formatphp->formatMessage([
            'id' => 'discountMessage',
            'defaultMessage' => 'Take {discount, number, ::percent} off the retail price!',
        ], [
            'discount' => 0.25,
        ]); // e.g., "Take 25% off the retail price!"

.. hint::
    See the sections on :ref:`formatting.dates` and :ref:`formatting.numbers`
    for other methods for localizing values.

.. _messages.rich-text:

Rich Text Formatting (Use of Tags in Messages)
##############################################

While the ICU message syntax does not prohibit the use of HTML tags in formatted
messages, HTML tags provide an added level of difficulty when it comes to parsing
and validating ICU formatted messages. By default, FormatPHP does not support
HTML tags in messages.

Instead, `like FormatJS`_, we support embedded rich text formatting using custom
tags and callbacks. This allows developers to embed as much text as possible so
sentences don't have to be broken up into chunks.

.. attention::
    These are not HTML or XML tags, and attributes are not supported.

.. code:: php

    echo $formatphp->formatMessage([
        'id' => 'priceMessage',
        'defaultMessage' => <<<'EOD'
            Our price is <boldThis>{price}</boldThis>
            with <link>{discount, number, ::percent} discount</link>
            EOD,
    ], [
        'price' => $formatphp->formatCurrency(29.99, 'USD', new Intl\NumberFormatOptions([
            'maximumFractionDigits' => 0,
        ])),
        'discount' => .025,
        'boldThis' => fn ($text) => "<strong>$text</strong>",
        'link' => fn ($text) => "<a href=\"/discounts/1234\">$text</a>",
    ]);

For an ``en-US`` locale, this will produce a string similar to the following:

.. code:: html

    Our price is <strong>$30</strong> with <a href="/discounts/1234">2.5% discount</a>

For rich text elements used throughout your application, you may provide a map
of tag names to rich text formatting functions, when configuring FormatPHP.

.. code:: php

    $config = new Config(
        new Intl\Locale('en-US'),
        null,
        [
            'em' => fn ($text) => "<em class=\"myClass\">$text</em>",
            'strong' => fn ($text) => "<strong class=\"myClass\">$text</strong>",
        ],
    );

Using this approach, consider the following formatted message:

.. code:: php

    $formatphp->formatMessage([
        'id' => 'welcome',
        'defaultMessage' => 'Welcome, <strong><em>{name}</em></strong>',
    ], [
        'name' => 'Sam',
    ]);

It will produce a string similar to the following:

.. code:: html

    Welcome, <strong class="myClass"><em class="myClass">Sam</em></strong>

.. _like FormatJS: https://formatjs.io/docs/core-concepts/icu-syntax#rich-text-formatting
.. _ICU message syntax: https://unicode-org.github.io/icu/userguide/format_parse/messages/
.. _pluralization and complex argument types: https://unicode-org.github.io/icu/userguide/format_parse/messages/#complex-argument-types
.. _formatting numbers: https://unicode-org.github.io/icu/userguide/format_parse/numbers/skeletons.html
.. _dates and times: https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
.. _ECMA-402, section 15.5.4: https://tc39.es/ecma402/#sec-partitionnumberpattern
.. _FormatJS: https://formatjs.io
