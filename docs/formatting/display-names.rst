.. _formatting.display-names:

========================
Formatting Display Names
========================

You may use the method ``formatDisplayName()`` to format the display names of
languages, regions, currency, and more. This returns a locale-appropriate,
translated string for the type requested.

.. tip::
    See the :ref:`reference.intl-displaynamesoptions` reference for more
    information on the options available.

All of the following examples use the locale es-ES.

Localize a Language Name
########################

Using the *language* ``type``, you may format a localized and translated display
name for any language tag.

.. code:: php

    echo $formatphp->formatDisplayName('en-US', new Intl\DisplayNamesOptions([
        'type' => 'language',
    ])); // e.g., "inglés (Estados Unidos)"

    echo $formatphp->formatDisplayName('zh-Hans-SG', new Intl\DisplayNamesOptions([
        'type' => 'language',
    ])); // e.g., "chino (simplificado, Singapur)"

Localize a Currency Name
########################

Using the *currency* ``type``, you may format a localized and translated display
name for any `ISO 4217 currency code`_.

.. code:: php

    echo $formatphp->formatDisplayName('EUR', new Intl\DisplayNamesOptions([
        'type' => 'currency',
    ])); // e.g., "euro"

    echo $formatphp->formatDisplayName('JPY', new Intl\DisplayNamesOptions([
        'type' => 'currency',
    ])); // e.g., "yen"

Localize a Region Name
######################

Using the *region* ``type``, you may format a localized and translated display
name for any region code.

.. code:: php

    echo $formatphp->formatDisplayName('GB', new Intl\DisplayNamesOptions([
        'type' => 'region',
    ])); // e.g., "Reino Unido"

    echo $formatphp->formatDisplayName('UN', new Intl\DisplayNamesOptions([
        'type' => 'region',
    ])); // e.g., "Naciones Unidas"

Localize a Script Name
######################

Using the *script* ``type``, you may format a localized and translated display
name for the script part of any language tag.

.. code:: php

    echo $formatphp->formatDisplayName('Latn', new Intl\DisplayNamesOptions([
        'type' => 'script',
    ])); // e.g., "latino"

    echo $formatphp->formatDisplayName('Cyrl', new Intl\DisplayNamesOptions([
        'type' => 'script',
    ])); // e.g., "cirílico"

.. _ISO 4217 currency code: https://www.iso.org/iso-4217-currency-codes.html
