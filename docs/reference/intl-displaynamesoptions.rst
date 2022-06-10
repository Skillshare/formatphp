.. _reference.intl-displaynamesoptions:

=========================
Intl\\DisplayNamesOptions
=========================

When formatting display names, you must provide a
``FormatPHP\Intl\DisplayNamesOptions`` instance with at least a ``type``
defined.

type
    The type of data for which we wish to format a display name. This
    currently supports *currency*, *language*, *region*, and *script*.

    .. note::
        While `ECMA-402`_ also defines *calendar* and *dateTimeField* as
        additional types, these types are not implemented in Node.js or any
        browsers. If set, these implementations throw exceptions, so FormatPHP
        follows the same pattern.

    .. code:: php

        echo $formatphp->formatDisplayName('USD', new Intl\DisplayNamesOptions([
            'type' => 'currency',
        ])); // e.g., "dólar estadounidense" when the locale is es-ES

fallback
    The fallback strategy to use. If we are unable to format a display name, we
    will return the same code provided if ``fallback`` is set to *code*. If
    ``fallback`` is *none*, then we return ``null``.

    The default is *code*.

    .. code:: php

        var_export($formatphp->formatDisplayName('FOO', new Intl\DisplayNamesOptions([
            'type' => 'currency',
            'fallback' => 'none',
        ]))); // e.g., NULL

style
    The formatting style to use: *long*, *short*, or *narrow*.

    This currently only affects the display name when ``type`` is *currency*.
    The default is *long*.

    .. code:: php

        echo $formatphp->formatDisplayName('USD', new Intl\DisplayNamesOptions([
            'type' => 'currency',
            'style' => 'narrow',
        ])); // e.g., "$"

languageDisplay
    This is a suggestion for displaying the language according to the locale's
    dialect or standard representation. In JavaScript, this defaults to
    *dialect*. For now, PHP supports only the *standard* representation, so
    *dialect* has no effect.

.. _ECMA-402: https://tc39.es/ecma402/#sec-intl-displaynames-constructor
