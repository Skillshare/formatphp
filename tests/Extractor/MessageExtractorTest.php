<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor;

use FormatPHP\Exception\UnableToProcessFile;
use FormatPHP\Extractor\MessageExtractor;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Test\TestCase;
use FormatPHP\Util\File;
use FormatPHP\Util\Globber;
use Generator;
use Hamcrest\Type\IsResource;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function json_decode;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

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

        $file = $this->mockery(File::class);

        $logger = $this->mockery(LoggerInterface::class);
        $logger->expects()->warning('Could not find files', ['files' => ['foo', 'bar', 'baz']]);

        $extractor = new MessageExtractor($options, $logger, $globber, $file);
        $extractor->process(['foo', 'bar', 'baz']);
    }

    public function testProcessBasic(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['formatMessage', 'translate'];

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), new File());

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
            ],
            $messages,
        );
    }

    public function testProcessWithFormatPhpFormatterName(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['formatMessage'];
        $options->format = 'FormatPHP';

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), new File());

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
            ],
            $messages,
        );
    }

    public function testProcessWithSimpleFormatterName(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['formatMessage'];
        $options->format = 'simple';

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), new File());

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        $output = ob_get_contents();
        ob_end_clean();

        $messages = json_decode((string) $output, true);

        $this->assertSame(
            [
                'aTestId' => 'This is a default message',
                'photos.count' => 'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
            ],
            $messages,
        );
    }

    public function testProcessWithCustomFormatterClass(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['formatMessage'];
        $options->format = CustomFormatter::class;

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), new File());

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
            ],
            $messages,
        );
    }

    public function testProcessWithExternalFormatterScript(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['formatMessage'];
        $options->format = __DIR__ . '/format.php';

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), new File());

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
            ],
            $messages,
        );
    }

    public function testProcessLogsErrorForInvalidFormatter(): void
    {
        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldReceive('error')->withArgs(function (string $message): bool {
            $expected = 'The format provided is not a known format, an instance of '
            . 'FormatPHP\\Writer\\Formatter\\Formatter, or a callable of the '
            . 'shape `callable(\\FormatPHP\\Intl\\DescriptorCollection,'
            . '\\FormatPHP\\Extractor\\MessageExtractorOptions):array<mixed>`.';

            return $message === $expected;
        });

        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['formatMessage'];
        $options->format = 'this-is-not-a-valid-formatter';

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), new File());

        ob_start();
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
        ob_end_clean();
    }

    public function testProcessWithNoResults(): void
    {
        $logger = new NullLogger();
        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['notExistentFunction'];

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), new File());

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
        $options->additionalFunctionNames = ['notExistentFunction'];
        $options->outFile = 'en-US.json';

        $file = $this->mockery(File::class);
        $file->shouldReceive('getContents')->andReturn('nothing of consequence');
        $file->expects()->writeContents('en-US.json', "{}\n");

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), $file);
        $extractor->process([__DIR__ . '/Parser/Descriptor/fixtures/*.php']);
    }

    public function testProcessWhenUnableToProcessFile(): void
    {
        $path = __DIR__ . '/Parser/Descriptor/fixtures/php-parser-01.php';

        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['formatMessage'];

        $exception = new UnableToProcessFile('something bad happened');

        $file = $this->mockery(File::class);
        $file->expects()->getContents($path)->andThrows($exception);
        $file->expects()->writeContents(new IsResource(), "{}\n");

        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldReceive('debug')->with(
            'Extracting from {file}',
            ['file' => $path],
        );
        $logger->shouldReceive('warning')->with(
            'something bad happened',
            ['exception' => $exception],
        );

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), $file);
        $extractor->process([$path]);
    }

    public function testProcessWhenUnableToProcessFileThrowsException(): void
    {
        $path = __DIR__ . '/Parser/Descriptor/fixtures/php-parser-01.php';

        $options = new MessageExtractorOptions();
        $options->additionalFunctionNames = ['formatMessage'];
        $options->throws = true;

        $exception = new UnableToProcessFile('something bad happened');

        $file = $this->mockery(File::class);
        $file->expects()->getContents($path)->andThrows($exception);

        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldReceive('debug')->with(
            'Extracting from {file}',
            ['file' => $path],
        );

        $extractor = new MessageExtractor($options, $logger, new Globber(new File()), $file);

        $this->expectException(UnableToProcessFile::class);
        $this->expectExceptionMessage('something bad happened');

        $extractor->process([$path]);
    }
}
