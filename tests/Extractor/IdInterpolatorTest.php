<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor;

use FormatPHP\Descriptor;
use FormatPHP\Exception\InvalidArgument;
use FormatPHP\Exception\UnableToGenerateMessageId;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\Descriptor as IntlDescriptor;
use FormatPHP\Test\TestCase;

class IdInterpolatorTest extends TestCase
{
    /**
     * @dataProvider generateIdProvider
     */
    public function testGenerateId(IntlDescriptor $descriptor, ?string $pattern, string $expectedId): void
    {
        $idInterpolator = new IdInterpolator();

        if ($pattern !== null) {
            $this->assertSame($expectedId, $idInterpolator->generateId($descriptor, $pattern));
        } else {
            $this->assertSame($expectedId, $idInterpolator->generateId($descriptor));
        }
    }

    /**
     * @return array<array{descriptor: IntlDescriptor, pattern: string | null, expectedId: string}>
     */
    public function generateIdProvider(): array
    {
        return [
            'descriptor has ID' => [
                'descriptor' => new Descriptor('aMessageId'),
                'pattern' => null,
                'expectedId' => 'aMessageId',
            ],
            'default message with default pattern' => [
                'descriptor' => new Descriptor(null, 'my default message'),
                'pattern' => null,
                'expectedId' => 'LbPcjH',
            ],
            'default message and description with default pattern' => [
                'descriptor' => new Descriptor(null, 'my default message', 'test description'),
                'pattern' => null,
                'expectedId' => 'R1aZay',
            ],
            'custom pattern with crc32, base64, and 8' => [
                'descriptor' => new Descriptor(null, '<<???>>'),
                'pattern' => '[crc32:contenthash:base64:8]',
                'expectedId' => 'ZSFCKQ==',
            ],
            'custom pattern with crc32, base64url, and 8' => [
                'descriptor' => new Descriptor(null, '<<???>>'),
                'pattern' => '[crc32:contenthash:base64url:8]',
                'expectedId' => 'ZSFCKQ',
            ],
            'custom pattern with sha3-384, base64, and 20' => [
                'descriptor' => new Descriptor(null, '<<???/>>>>'),
                'pattern' => '[sha3-384:contenthash:base64:20]',
                'expectedId' => 'WC+Ex4/ILCD7Lb6tRv7x',
            ],
            'custom pattern with sha3-384, base64url, and 20' => [
                'descriptor' => new Descriptor(null, '<<???/>>>>'),
                'pattern' => '[sha3-384:contenthash:base64url:20]',
                'expectedId' => 'WC-Ex4_ILCD7Lb6tRv7x',
            ],
            'custom pattern with md5, hex, and 6' => [
                'descriptor' => new Descriptor(null, 'some message'),
                'pattern' => '[md5:contenthash:hex:6]',
                'expectedId' => 'df49b6',
            ],
            'custom pattern using hash instead of contenthash' => [
                'descriptor' => new Descriptor(null, 'some message'),
                'pattern' => '[md5:hash:hex:6]',
                'expectedId' => 'df49b6',
            ],
        ];
    }

    public function testGenerateIdThrowsExceptionWhenUnableToGenerateMessageId(): void
    {
        $idInterpolator = new IdInterpolator();

        $this->expectException(UnableToGenerateMessageId::class);
        $this->expectExceptionMessage(
            'To auto-generate a message ID, the message descriptor must '
            . 'have a default message and, optionally, a description.',
        );

        $idInterpolator->generateId(new Descriptor(null, null, 'a description'));
    }

    public function testGenerateIdThrowsExceptionWhenInterpolationPatternIsInvalid(): void
    {
        $idInterpolator = new IdInterpolator();

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Pattern is not a valid ID interpolation pattern: "[sha256:something:base64:6]".',
        );

        $idInterpolator->generateId(new Descriptor(null, 'foo'), '[sha256:something:base64:6]');
    }

    public function testGenerateIdThrowsExceptionWhenHashingAlgorithmIsInvalid(): void
    {
        $idInterpolator = new IdInterpolator();

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Unknown or unsupported hashing algorithm: "foobar".',
        );

        $idInterpolator->generateId(new Descriptor(null, 'foo'), '[foobar:hash:base64:6]');
    }

    public function testGenerateIdThrowsExceptionWhenEncodingAlgorithmIsInvalid(): void
    {
        $idInterpolator = new IdInterpolator();

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Unknown or unsupported encoding algorithm: "baz".',
        );

        $idInterpolator->generateId(new Descriptor(null, 'foo'), '[md5:hash:baz:6]');
    }
}
