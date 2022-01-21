<?php

declare(strict_types=1);

namespace FormatPHP\Test\PseudoLocale;

use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\PseudoLocale\Converter;
use FormatPHP\PseudoLocale\ConverterOptions;
use FormatPHP\Test\TestCase;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\FormatHelper;
use Psr\Log\LoggerInterface;

class ConverterTest extends TestCase
{
    /**
     * @dataProvider providePseudoLocales
     */
    public function testConvert(string $file, string $pseudoLocale, ConverterOptions $options): void
    {
        $outFile = $options->outFile ?? 'php://output';

        $fileSystemHelper = $this->mockery(FileSystemHelper::class)->makePartial();
        $fileSystemHelper->shouldReceive('writeContents')->withArgs(
            function (string $file, string $contents) use ($outFile): bool {
                $this->assertSame($outFile, $file);

                // Use a text snapshot because the JSON snapshot tool encodes
                // UTF-8 characters, which are harder for humans to read.
                $this->assertMatchesTextSnapshot($contents);

                return true;
            },
        );

        $logger = $this->mockery(LoggerInterface::class);
        if ($options->outFile !== null) {
            $logger->expects()->notice(
                'Messages converted to pseudo locale {locale} and written to {file}',
                [
                    'locale' => $pseudoLocale,
                    'file' => $outFile,
                ],
            );
        }

        $converter = new Converter(
            $options,
            $fileSystemHelper,
            new FormatHelper($fileSystemHelper),
            $logger,
        );

        $converter->convert($file, $pseudoLocale);
    }

    /**
     * @return array<string, array{file: string, pseudoLocale: string}>
     */
    public function providePseudoLocales(): array
    {
        return [
            'pseudo locale en-XA' => [
                'file' => __DIR__ . '/../fixtures/locales/en.json',
                'pseudoLocale' => 'en-XA',
                'options' => new ConverterOptions(),
            ],
            'pseudo locale en-XB' => [
                'file' => __DIR__ . '/../fixtures/locales/en.json',
                'pseudoLocale' => 'en-XB',
                'options' => new ConverterOptions(),
            ],
            'pseudo locale xx-AC' => [
                'file' => __DIR__ . '/../fixtures/locales/en.json',
                'pseudoLocale' => 'xx-AC',
                'options' => new ConverterOptions(),
            ],
            'pseudo locale xx-HA' => [
                'file' => __DIR__ . '/../fixtures/locales/en.json',
                'pseudoLocale' => 'xx-HA',
                'options' => new ConverterOptions(),
            ],
            'pseudo locale xx-LS' => [
                'file' => __DIR__ . '/../fixtures/locales/en.json',
                'pseudoLocale' => 'xx-LS',
                'options' => new ConverterOptions(),
            ],
            'pseudo locale with no messages' => [
                'file' => __DIR__ . '/../fixtures/locales/no-messages.json',
                'pseudoLocale' => 'en-XA',
                'options' => new ConverterOptions(),
            ],
            'pseudo locale en-xa with outFile' => [
                'file' => __DIR__ . '/../fixtures/locales/en.json',
                'pseudoLocale' => 'en-xa',
                'options' => (function (): ConverterOptions {
                    $options = new ConverterOptions();
                    $options->outFile = 'foo.json';

                    return $options;
                })(),
            ],
            'pseudo locale EN-xb in smartling format' => [
                'file' => __DIR__ . '/../fixtures/locales/en.smartling.json',
                'pseudoLocale' => 'EN-xb',
                'options' => (function (): ConverterOptions {
                    $options = new ConverterOptions();
                    $options->inFormat = 'smartling';
                    $options->outFormat = 'smartling';

                    return $options;
                })(),
            ],
            'pseudo locale XX-AC in smartling, out simple' => [
                'file' => __DIR__ . '/../fixtures/locales/en.smartling.json',
                'pseudoLocale' => 'XX-AC',
                'options' => (function (): ConverterOptions {
                    $options = new ConverterOptions();
                    $options->inFormat = 'smartling';
                    $options->outFormat = 'simple';

                    return $options;
                })(),
            ],
        ];
    }

    public function testConvertThrowsExceptionForUnknownPseudoLocale(): void
    {
        $fileSystemHelper = new FileSystemHelper();
        $logger = $this->mockery(LoggerInterface::class);

        $converter = new Converter(
            new ConverterOptions(),
            $fileSystemHelper,
            new FormatHelper($fileSystemHelper),
            $logger,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown pseudo locale "en"');

        $converter->convert(__DIR__ . '/../fixtures/locales/en.json', 'en');
    }

    /**
     * Zalgo is non-deterministic, so we can't use snapshot testing for it.
     */
    public function testZalgo(): void
    {
        $outFile = 'php://output';

        $fileSystemHelper = $this->mockery(FileSystemHelper::class)->makePartial();
        $fileSystemHelper->shouldReceive('writeContents')->withArgs(
            function (string $file, string $contents) use ($outFile): bool {
                $this->assertSame($outFile, $file);

                // We're unable to deterministically test the $contents, since
                // Zalgo text changes each time it's generated.

                return true;
            },
        );

        $logger = $this->mockery(LoggerInterface::class);

        $converter = new Converter(
            new ConverterOptions(),
            $fileSystemHelper,
            new FormatHelper($fileSystemHelper),
            $logger,
        );

        $converter->convert(__DIR__ . '/../fixtures/locales/en.json', 'xx-ZA');
    }
}
