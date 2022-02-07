.. _tms:

===========
TMS Support
===========

A `translation management system`_, or TMS, allows translators to use your
default locale file to create translations for all the other languages your
application supports. To work with a TMS, you will :ref:`extract the formatted
strings <string-extraction.extract>` from your application to send to the TMS.
Often, a TMS will specify a particular document format they require.

To extract strings in a specific TMS format, use the ``--format`` option.

.. code:: shell

    ./vendor/bin/formatphp extract \
        --format=simple \
        --out-file=locales/en.json \
        'src/**/*.php'

Out of the box, FormatPHP supports the following formatters for integration with
third-party TMSes. Supporting a TMS does not imply endorsement of that
particular TMS.

.. list-table:: Translation management systems
    :header-rows: 1

    * - TMS
      - ``--format=``
    * - `Crowdin Chrome JSON`_
      - ``crowdin``
    * - `Lingohub`_
      - ``simple``
    * - `locize`_
      - ``simple``
    * - `Phrase`_
      - ``simple``
    * - `SimpleLocalize`_
      - ``simple``
    * - `Smartling ICU JSON`_
      - ``smartling``

Our default formatter is ``formatphp``, which mirrors the output of the default
formatter for FormatJS.

Custom Formatters
#################

You may provide your own formatter using our interfaces. You will need to
create a writer for the format. Optionally, you may create a reader, if using
our message loader or the ``formatphp pseudo-locale`` command with the
``--in-format`` option.

The *writer* must implement ``FormatPHP\Format\WriterInterface`` or be a
callable of the shape:

.. code:: php

    callable(FormatPHP\DescriptorCollection, FormatPHP\Format\WriterOptions): mixed[]

The *reader* must implement ``FormatPHP\Format\ReaderInterface`` or be a
callable of the shape:

.. code:: php

    callable(mixed[]): FormatPHP\MessageCollection

To use your custom writer with the ``formatphp extract`` CLI tool, pass the
fully-qualified class name to ``--format``, or a path to a script that returns
the callable.

For example, given the script ``my-writer.php`` with the following contents:

.. code:: php

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

You may call ``formatphp extract`` like this:

.. code:: shell

    ./vendor/bin/formatphp extract \
        --format='path/to/my-writer.php' \
        --out-file=locales/en.json \
        'src/**/*.php'

Then, to use a custom reader with the message loader, you may do something like
the following:

.. code:: php

    $messageLoader = new \FormatPHP\MessageLoader(

        // The path to your locale JSON files (i.e., en.json, es.json, etc.).
        '/path/to/app/locales',

        // The configuration object created earlier.
        $config,

        // Pass your custom reader through the formatReader parameter.
        MyCustomReader::class,

    );

The ``formatReader`` parameter of the ``MessageLoader`` constructor accepts the
following:

* Fully-qualified class name for a class that implements
  ``FormatPHP\Format\ReaderInterface``
* An already-instantiated instance object of
  ``FormatPHP\Format\ReaderInterface``
* A callable with the shape ``callable(mixed[]): FormatPHP\MessageCollection``
* The path to a script that returns a callable with this shape

.. _translation management system: https://en.wikipedia.org/wiki/Translation_management_system
.. _Crowdin Chrome JSON: https://support.crowdin.com/file-formats/chrome-json/
.. _Lingohub: https://lingohub.com/developers/resource-files/json-localization/
.. _locize: https://docs.locize.com/integration/supported-formats#json-flatten
.. _Phrase: https://help.phrase.com/help/simple-json
.. _SimpleLocalize: https://simplelocalize.io/docs/integrations/format-js-cli/
.. _Smartling ICU JSON: https://help.smartling.com/hc/en-us/articles/360008000733-JSON
