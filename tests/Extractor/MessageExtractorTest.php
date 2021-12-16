<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor;

use FormatPHP\DescriptorCollection;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Extractor\MessageExtractor;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Extractor\Parser\DescriptorParserInterface;
use FormatPHP\Extractor\Parser\ParserErrorCollection;
use FormatPHP\Icu\MessageFormat\Parser\Exception\InvalidMessageException;
use FormatPHP\Test\TestCase;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\FormatHelper;
use FormatPHP\Util\Globber;
use Generator;
use Hamcrest\Core\IsInstanceOf;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use stdClass;

use function count;
use function json_decode;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function sprintf;

class MessageExtractorTest extends TestCase
{
    public function testProcessWhenNoFilesAreFound(): void
    {
        $options = new MessageExtractorOptions();

        $globber = $this->mockery(Globber::class);
        $globber->shouldReceive('find')->andReturnUsing(function (): Generator {
            // @phpstan-ignore-next-line
            foreach ([] as $item) {
                yield $item;
            }
        });

        $file = $this->mockery(FileSystemHelper::class);

        $logger = $this->mockery(LoggerInterface::class);
        $logger->expects()->warning('Could not find files', ['files' => ['foo', 'bar', 'baz']]);

        $extractor = new MessageExtractor($options, $logger, $globber, $file, new FormatHelper($file));
        $extractor->process(['foo', 'bar', 'baz']);
    }

    public function testProcessBasic(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->functionNames = ['formatMessage', 'translate'];

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.ph*']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'OpKKos' => [
                    'defaultMessage' => 'Hello!',
                ],
                'photos.count' => [
                    'defaultMessage' =>
                        'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                    'description' => 'A description with multiple lines and extra whitespace.',
                ],
                'welcome' => [
                    'defaultMessage' => 'Welcome!',
                ],
                'goodbye' => [
                    'defaultMessage' => 'Goodbye!',
                ],
                'Soex4s' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'xgMWoP' => [
                    'defaultMessage' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'defaultMessage' => 'Welcome!',
                ],
            ],
            $messages,
        );
    }

    public function testProcessWithFormatPhpFormatterName(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->format = 'FormatPHP';

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'photos.count' => [
                    'defaultMessage' =>
                        'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                    'description' => 'A description with multiple lines and extra whitespace.',
                ],
                'Soex4s' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'xgMWoP' => [
                    'defaultMessage' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'defaultMessage' => 'Welcome!',
                ],
            ],
            $messages,
        );
    }

    public function testProcessWithSimpleFormatterName(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->format = 'simple';

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => 'This is a default message',
                'photos.count' => 'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                'Soex4s' => 'This is a default message',
                'xgMWoP' => 'This is a default message',
                'Q+U0TW' => 'Welcome!',
            ],
            $messages,
        );
    }

    public function testProcessWithSmartlingFormatterName(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->format = 'smartling';

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'smartling' => [
                    'string_format' => 'icu',
                    'translate_paths' => [
                        [
                            'instruction' => '*/description',
                            'key' => '{*}/message',
                            'path' => '*/message',
                        ],
                    ],
                    'variants_enabled' => true,
                ],
                'aTestId' => [
                    'description' => 'A simple description of a fixture for testing purposes.',
                    'message' => 'This is a default message',
                ],
                'photos.count' => [
                    'description' => 'A description with multiple lines and extra whitespace.',
                    'message' => 'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                ],
                'Soex4s' => [
                    'description' => 'A simple description of a fixture for testing purposes.',
                    'message' => 'This is a default message',
                ],
                'xgMWoP' => [
                    'message' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'message' => 'Welcome!',
                ],
            ],
            $messages,
        );
    }

    public function testProcessWithCrowdinFormatterName(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->format = 'crowdin';

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'description' => 'A simple description of a fixture for testing purposes.',
                    'message' => 'This is a default message',
                ],
                'photos.count' => [
                    'description' => 'A description with multiple lines and extra whitespace.',
                    'message' => 'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                ],
                'Soex4s' => [
                    'description' => 'A simple description of a fixture for testing purposes.',
                    'message' => 'This is a default message',
                ],
                'xgMWoP' => [
                    'message' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'message' => 'Welcome!',
                ],
            ],
            $messages,
        );
    }

    public function testProcessWithChromeFormatterName(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->format = 'chrome';

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'description' => 'A simple description of a fixture for testing purposes.',
                    'message' => 'This is a default message',
                ],
                'photos.count' => [
                    'description' => 'A description with multiple lines and extra whitespace.',
                    'message' => 'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                ],
                'Soex4s' => [
                    'description' => 'A simple description of a fixture for testing purposes.',
                    'message' => 'This is a default message',
                ],
                'xgMWoP' => [
                    'message' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'message' => 'Welcome!',
                ],
            ],
            $messages,
        );
    }

    public function testProcessWithCustomFormatterClass(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->format = CustomFormat::class;

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'id' => 'aTestId',
                    'string' => 'This is a default message',
                ],
                'photos.count' => [
                    'id' => 'photos.count',
                    'string' => 'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                ],
                'Soex4s' => [
                    'id' => 'Soex4s',
                    'string' => 'This is a default message',
                ],
                'xgMWoP' => [
                    'id' => 'xgMWoP',
                    'string' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'id' => 'Q+U0TW',
                    'string' => 'Welcome!',
                ],
            ],
            $messages,
        );
    }

    public function testProcessWithExternalFormatterScript(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->format = __DIR__ . '/fixtures/formatter.php';

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'translation' => 'This is a default message',
                ],
                'photos.count' => [
                    'translation' => 'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                ],
                'Soex4s' => [
                    'translation' => 'This is a default message',
                ],
                'xgMWoP' => [
                    'translation' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'translation' => 'Welcome!',
                ],
            ],
            $messages,
        );
    }

    public function testProcessLogsErrorForInvalidFormatter(): void
    {
        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldReceive('error')->withArgs(function (string $message): bool {
            $expected = 'The format provided is not a known format, an instance of '
            . 'FormatPHP\\Format\\WriterInterface, or a callable of the '
            . 'shape `callable(FormatPHP\\DescriptorCollection,'
            . 'FormatPHP\\Format\\WriterOptions):array<mixed>`.';

            return $message === $expected;
        });

        $options = new MessageExtractorOptions();
        $options->format = 'this-is-not-a-valid-formatter';

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        ob_end_clean();
    }

    public function testProcessWithNoResults(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->functionNames = ['notExistentFunction'];

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('{}', (string) $output);
    }

    public function testProcessWritesToFile(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->functionNames = ['notExistentFunction'];
        $options->outFile = 'en-US.json';

        $file = $this->mockery(FileSystemHelper::class);
        $file->shouldReceive('getContents')->andReturn('nothing of consequence');
        $file->expects()->writeJsonContents('en-US.json', new IsInstanceOf(stdClass::class));

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            $file,
            new FormatHelper(new FileSystemHelper()),
        );

        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
    }

    public function testProcessWhenUnableToProcessFile(): void
    {
        $path = __DIR__ . '/Parser/Descriptor/fixtures/php-parser-01.php';

        $options = new MessageExtractorOptions();

        $exception = new UnableToProcessFileException('something bad happened');

        $file = $this->mockery(FileSystemHelper::class);
        $file->expects()->getContents($path)->andThrows($exception);
        $file->expects()->writeJsonContents('php://output', new IsInstanceOf(stdClass::class));

        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldReceive('debug')->with(
            'Extracting from {file}',
            ['file' => $path],
        );
        $logger->shouldReceive('warning')->with(
            'something bad happened',
            ['exception' => $exception],
        );

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            $file,
            new FormatHelper(new FileSystemHelper()),
        );

        $extractor->process([$path]);
    }

    public function testProcessWhenUnableToProcessFileThrowsException(): void
    {
        $path = __DIR__ . '/Parser/Descriptor/fixtures/php-parser-01.php';

        $options = new MessageExtractorOptions();
        $options->throws = true;

        $exception = new UnableToProcessFileException('something bad happened');

        $file = $this->mockery(FileSystemHelper::class);
        $file->expects()->getContents($path)->andThrows($exception);

        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldReceive('debug')->with(
            'Extracting from {file}',
            ['file' => $path],
        );

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            $file,
            new FormatHelper(new FileSystemHelper()),
        );

        $this->expectException(UnableToProcessFileException::class);
        $this->expectExceptionMessage('something bad happened');

        $extractor->process([$path]);
    }

    public function testProcessWithCustomParser(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->parsers = [CustomDescriptorParser::class, 'php'];

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([
            __DIR__ . '/Parser/Descriptor/fixtures/*.ph*',
            __DIR__ . '/Parser/Descriptor/fixtures/*.template',
        ]);
        $output = ob_get_contents();
        ob_end_clean();

        $errors = $extractor->getErrors()->toArray();

        $receivedErrors = [];
        foreach ($errors as $error) {
            $receivedErrors[] = $error->message . ' in ' . $error->sourceFile . ' on line ' . (int) $error->sourceLine;
        }

        $expectedErrors = [
            'Descriptor argument must be an array in ' . __DIR__
                . '/Parser/Descriptor/fixtures/php-parser-02.php on line 32',
            'Descriptor argument must be an array in ' . __DIR__
                . '/Parser/Descriptor/fixtures/php-parser-03.php on line 8',
            'Descriptor argument must be an array in ' . __DIR__
                . '/Parser/Descriptor/fixtures/php-parser-04.php on line 40',
            'Descriptor argument must be present in ' . __DIR__
                . '/Parser/Descriptor/fixtures/php-parser-09.phtml on line 18',
            'The descriptor must not contain values other than string literals; '
                . 'encountered Expr_Variable in ' . __DIR__
                . '/Parser/Descriptor/fixtures/php-parser-10.php on line 6',
            'The descriptor must not contain values other than string literals; '
                . 'encountered Scalar_Encapsed in ' . __DIR__
                . '/Parser/Descriptor/fixtures/php-parser-10.php on line 12',
            'Missing "defaultMessage" in "{{#formatMessage |idWithoutMessage}}{{/formatMessage}}" in '
                . __DIR__ . '/Parser/Descriptor/fixtures/custom-parser-01.template on line 0',
            'Missing "id" in "{{#formatMessage}}message without ID{{/formatMessage}}" in '
                . __DIR__ . '/Parser/Descriptor/fixtures/custom-parser-01.template on line 0',
        ];

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'photos.count' => [
                    'defaultMessage' =>
                        'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                    'description' => 'A description with multiple lines and extra whitespace.',
                ],
                'welcome' => [
                    'defaultMessage' => 'Welcome!',
                ],
                'goodbye' => [
                    'defaultMessage' => 'Goodbye!',
                ],
                'Soex4s' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'xgMWoP' => [
                    'defaultMessage' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'defaultMessage' => 'Welcome!',
                ],
                'customWelcome' => [
                    'defaultMessage' => 'Custom Welcome!',
                ],
                'customGoodbye' => [
                    'defaultMessage' => 'Custom Goodbye!',
                ],
            ],
            $messages,
        );

        $this->assertSame($expectedErrors, $receivedErrors);
    }

    public function testProcessWithCustomParserAsClosure(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->parsers = ['php', __DIR__ . '/parser.php'];

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([
            __DIR__ . '/Parser/Descriptor/fixtures/*.ph*',
            __DIR__ . '/Parser/Descriptor/fixtures/*.template',
        ]);
        $output = ob_get_contents();
        ob_end_clean();

        $errors = $extractor->getErrors()->toArray();

        $receivedErrors = [];
        foreach ($errors as $error) {
            $receivedErrors[] = $error->message . ' in ' . $error->sourceFile . ' on line ' . (int) $error->sourceLine;
        }

        $expectedErrors = [
            'Descriptor argument must be an array in ' . __DIR__
            . '/Parser/Descriptor/fixtures/php-parser-02.php on line 32',
            'Descriptor argument must be an array in ' . __DIR__
            . '/Parser/Descriptor/fixtures/php-parser-03.php on line 8',
            'Descriptor argument must be an array in ' . __DIR__
            . '/Parser/Descriptor/fixtures/php-parser-04.php on line 40',
            'Descriptor argument must be present in ' . __DIR__
            . '/Parser/Descriptor/fixtures/php-parser-09.phtml on line 18',
            'The descriptor must not contain values other than string literals; '
            . 'encountered Expr_Variable in ' . __DIR__
            . '/Parser/Descriptor/fixtures/php-parser-10.php on line 6',
            'The descriptor must not contain values other than string literals; '
            . 'encountered Scalar_Encapsed in ' . __DIR__
            . '/Parser/Descriptor/fixtures/php-parser-10.php on line 12',
            'Missing "defaultMessage" in "{{#formatMessage |idWithoutMessage}}{{/formatMessage}}" in '
            . __DIR__ . '/Parser/Descriptor/fixtures/custom-parser-01.template on line 0',
            'Missing "id" in "{{#formatMessage}}message without ID{{/formatMessage}}" in '
            . __DIR__ . '/Parser/Descriptor/fixtures/custom-parser-01.template on line 0',
        ];

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'photos.count' => [
                    'defaultMessage' =>
                        'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                    'description' => 'A description with multiple lines and extra whitespace.',
                ],
                'welcome' => [
                    'defaultMessage' => 'Welcome!',
                ],
                'goodbye' => [
                    'defaultMessage' => 'Goodbye!',
                ],
                'Soex4s' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'xgMWoP' => [
                    'defaultMessage' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'defaultMessage' => 'Welcome!',
                ],
                'customWelcome' => [
                    'defaultMessage' => 'Custom Welcome!',
                ],
                'customGoodbye' => [
                    'defaultMessage' => 'Custom Goodbye!',
                ],
            ],
            $messages,
        );

        $this->assertSame($expectedErrors, $receivedErrors);
    }

    public function testProcessThrowsExceptionWithCustomParserNotACallable(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->parsers = ['php', __DIR__ . '/../Util/fixtures/load-closure-04.php'];

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The parser provided is not a known descriptor parser, an instance of '
            . '%s, or a callable of the shape `callable(string,%s,%s):%s`.',
            DescriptorParserInterface::class,
            MessageExtractorOptions::class,
            ParserErrorCollection::class,
            DescriptorCollection::class,
        ));

        $extractor->process([
            __DIR__ . '/Parser/Descriptor/fixtures/*.ph*',
            __DIR__ . '/Parser/Descriptor/fixtures/*.template',
        ]);
    }

    public function testProcessFlatten(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->functionNames = ['formatMessage', 'translate'];
        $options->flatten = true;

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.ph*']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'OpKKos' => [
                    'defaultMessage' => 'Hello!',
                ],
                'photos.count' => [
                    'defaultMessage' => '{numPhotos, plural, =0{You have no photos.} '
                        . '=1{You have one photo.} other{You have # photos.}}',
                    'description' => 'A description with multiple lines and extra whitespace.',
                ],
                'welcome' => [
                    'defaultMessage' => 'Welcome!',
                ],
                'goodbye' => [
                    'defaultMessage' => 'Goodbye!',
                ],
                'Soex4s' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'xgMWoP' => [
                    'defaultMessage' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'defaultMessage' => 'Welcome!',
                ],
            ],
            $messages,
        );
    }

    public function testProcessValidate(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->validateMessages = true;
        $options->functionNames = ['formatMessage', 'translate'];

        $extractor = new MessageExtractor(
            $options,
            $logger,
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
            new FormatHelper(new FileSystemHelper()),
        );

        ob_start();
        $extractor->process([
            __DIR__ . '/Parser/Descriptor/fixtures/*.ph*',
            __DIR__ . '/../fixtures/invalid-message.php',
        ]);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => [
                    'defaultMessage' => 'This is a default <a href="#foo">message</a>',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'OpKKos' => [
                    'defaultMessage' => 'Hello!',
                ],
                'photos.count' => [
                    'defaultMessage' =>
                        'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                    'description' => 'A description with multiple lines and extra whitespace.',
                ],
                'welcome' => [
                    'defaultMessage' => 'Welcome!',
                ],
                'goodbye' => [
                    'defaultMessage' => 'Goodbye!',
                ],
                'Soex4s' => [
                    'defaultMessage' => 'This is a default message',
                    'description' => 'A simple description of a fixture for testing purposes.',
                ],
                'xgMWoP' => [
                    'defaultMessage' => 'This is a default message',
                ],
                'Q+U0TW' => [
                    'defaultMessage' => 'Welcome!',
                ],
            ],
            $messages,
        );

        $this->assertGreaterThan(0, count($extractor->getErrors()));

        $errors = [];
        foreach ($extractor->getErrors() as $error) {
            $message = $error->message;
            if ($error->exception instanceof InvalidMessageException) {
                $message = 'Syntax Error: '
                    . $error->exception->getParserError()->getErrorKindName()
                    . ' in message "' . $error->exception->getParserError()->message . '"';
            }

            $errors[$error->sourceFile][] = [$error->sourceLine, $message];
        }

        $this->assertSame([
            __DIR__ . '/Parser/Descriptor/fixtures/php-parser-02.php' => [
                [32, 'Descriptor argument must be an array'],
            ],
            __DIR__ . '/Parser/Descriptor/fixtures/php-parser-03.php' => [
                [8, 'Descriptor argument must be an array'],
            ],
            __DIR__ . '/Parser/Descriptor/fixtures/php-parser-04.php' => [
                [29, 'Descriptor argument must be an array'],
                [40, 'Descriptor argument must be an array'],
            ],
            __DIR__ . '/Parser/Descriptor/fixtures/php-parser-09.phtml' => [
                [18, 'Descriptor argument must be present'],
            ],
            __DIR__ . '/Parser/Descriptor/fixtures/php-parser-10.php' => [
                [6, 'The descriptor must not contain values other than string literals; encountered Expr_Variable'],
                [12, 'The descriptor must not contain values other than string literals; encountered Scalar_Encapsed'],
            ],
            __DIR__ . '/../fixtures/invalid-message.php' => [
                [4, 'Syntax Error: INVALID_TAG in message "This is a default <a href="#foo">message</a>"'],
            ],
        ], $errors);
    }
}
