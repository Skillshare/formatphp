<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat;

use FormatPHP\Icu\MessageFormat\Manipulator;
use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Icu\MessageFormat\Printer;
use FormatPHP\Test\TestCase;

use function assert;

class ManipulatorTest extends TestCase
{
    /**
     * @dataProvider messageProvider
     */
    public function testHoistSelectors(string $message): void
    {
        $parser = new Parser($message);
        $result = $parser->parse();

        assert($result->val !== null);

        $manipulator = new Manipulator();
        $hoistedAst = $manipulator->hoistSelectors($result->val);

        $printer = new Printer();

        $this->assertMatchesTextSnapshot($printer->printAst($hoistedAst));
    }

    /**
     * @return array<string, array{message: string}>
     */
    public function messageProvider(): array
    {
        return [
            'should hoist 1 plural' => [
                'message' => 'I have {count, plural, one{a dog} other{many dogs}}',
            ],
            'hoist some random case 1' => [
                'message' => '{p1, plural, one{one {foo, select, bar{two} baz{three} other{other}}} other{other}}',
            ],
            'should hoist plural & select and tag' => [
                'message' => <<<'EOD'
                    I have {count, plural,
                        one{a {
                            gender, select,
                                male{male}
                                female{female}
                                other{male}
                            } <b>dog</b>
                        }
                        other{many dogs}} and {count, plural,
                            one{a {
                                gender, select,
                                    male{male}
                                    female{female}
                                    other{male}
                                } <strong>cat</strong>
                            }
                            other{many cats}}
                    EOD,
            ],
        ];
    }
}
