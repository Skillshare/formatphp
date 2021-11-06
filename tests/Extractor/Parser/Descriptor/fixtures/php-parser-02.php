<?php
/**
 * @intl some:thing this should not be captured another:meta-value also not captured
 */

use FormatPHP\FormatPHP;

/**
 * The following pragma annotation is empty on purpose
 *
 * @intl
 *
 * Just some sample comment text here.
 * @intl more:details and:more
 * More comments not part of the pragma.
 */
class Foo
{
    private FormatPHP $intl;

    /**
     * @intl another_property:some_value
     */
    public function __construct()
    {
        $this->intl = new FormatPHP('en', []);
    }

    public function bar(array $descriptor): string
    {
        // @intl and-still-more:a-value
        return $this->intl->formatMessage($descriptor);
    }

    public function baz(): void
    {
        $translation = $this->bar([
            'id' => 'greeting.question',
            'defaultMessage' => 'How are you?',
        ]);

        echo $translation . "\n";
    }
}
