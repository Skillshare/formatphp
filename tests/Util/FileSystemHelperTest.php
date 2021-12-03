<?php

declare(strict_types=1);

namespace FormatPHP\Test\Util;

use Closure;
use FormatPHP\Exception\ImproperContextException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Exception\UnableToWriteFileException;
use FormatPHP\Test\TestCase;
use FormatPHP\Util\FileSystemHelper;

use function assert;
use function file_get_contents;
use function fopen;
use function fread;
use function fseek;
use function getcwd;
use function is_resource;
use function is_writable;
use function realpath;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function tmpfile;
use function unlink;

class FileSystemHelperTest extends TestCase
{
    public function testGetContentsReturnsFileContents(): void
    {
        $file = new FileSystemHelper();
        $contents = $file->getContents(__FILE__);

        $this->assertStringContainsString('FileTest', $contents);
    }

    public function testGetContentsThrowsExceptionForUnreadableFile(): void
    {
        $file = new FileSystemHelper();

        $this->expectException(UnableToProcessFileException::class);
        $this->expectExceptionMessage('File does not exist or you do not have permission to read it: "foo-bar.txt".');

        $file->getContents('foo-bar.txt');
    }

    public function testGetContentsThrowsExceptionForDirectory(): void
    {
        $file = new FileSystemHelper();

        $this->expectException(UnableToProcessFileException::class);
        $this->expectExceptionMessage(sprintf('File path is a directory: "%s".', __DIR__));

        $file->getContents(__DIR__);
    }

    public function testGetContentsThrowsExceptionWhenReadingOtherwiseFails(): void
    {
        $file = $this->mockery(FileSystemHelper::class, [
            'isReadable' => true,
            'isDirectory' => false,
        ]);
        $file->shouldReceive('getContents')->passthru();

        $this->expectException(UnableToProcessFileException::class);
        $this->expectExceptionMessage('Could not open file for reading: "whatever.txt".');

        $file->getContents('whatever.txt');
    }

    public function testIsDirectory(): void
    {
        $file = new FileSystemHelper();

        $this->assertTrue($file->isDirectory(__DIR__));
    }

    public function testIsReadable(): void
    {
        $file = new FileSystemHelper();

        $this->assertTrue($file->isReadable(__FILE__));
    }

    public function testGetCurrentWorkingDirectory(): void
    {
        $file = new FileSystemHelper();
        $cwd = $file->getCurrentWorkingDirectory();

        $this->assertSame(getcwd() . '/', $cwd);

        // Try it again for coverage skipping the `if` statement.
        $this->assertSame($cwd, $file->getCurrentWorkingDirectory());
    }

    public function testLoadClosureFromScriptThrowsExceptionWhenNotUsingCliSapi(): void
    {
        $file = $this->mockery(FileSystemHelper::class);
        $file->shouldAllowMockingProtectedMethods();
        $file->shouldReceive('getSapiName')->andReturn('not-cli');
        $file->shouldReceive('loadClosureFromScript')->passthru();

        $this->expectException(ImproperContextException::class);
        $this->expectExceptionMessage(
            'Method must be called from CLI SAPI context only; called from not-cli context.',
        );

        $file->loadClosureFromScript('/path/to/script.php');
    }

    public function testLoadClosureFromScriptThrowsExceptionWhenPathIsNotReadable(): void
    {
        $file = $this->mockery(FileSystemHelper::class);
        $file->shouldAllowMockingProtectedMethods();
        $file->shouldReceive('getSapiName')->andReturn('cli');
        $file->shouldReceive('loadClosureFromScript')->passthru();
        $file->expects()->isReadable('/path/to/script.php')->andReturnFalse();

        $this->assertNull($file->loadClosureFromScript('/path/to/script.php'));
    }

    public function testLoadClosureFromScriptThrowsExceptionWhenPathIsADirectory(): void
    {
        $file = $this->mockery(FileSystemHelper::class);
        $file->shouldAllowMockingProtectedMethods();
        $file->shouldReceive('getSapiName')->andReturn('cli');
        $file->shouldReceive('loadClosureFromScript')->passthru();
        $file->expects()->isReadable('/path/to/script.php')->andReturnTrue();
        $file->expects()->isDirectory('/path/to/script.php')->andReturnTrue();

        $this->assertNull($file->loadClosureFromScript('/path/to/script.php'));
    }

    /**
     * @dataProvider loadClosureFromScriptProvider
     */
    public function testLoadClosureFromScript(string $path, bool $expectClosure): void
    {
        $file = new FileSystemHelper();
        $closure = $file->loadClosureFromScript($path);

        if ($expectClosure === true) {
            $this->assertInstanceOf(Closure::class, $closure);
        } else {
            $this->assertNull($closure);
        }
    }

    /**
     * @return array<array{path: string, expectClosure: bool}>
     */
    public function loadClosureFromScriptProvider(): array
    {
        return [
            [
                'path' => __DIR__ . '/fixtures/load-closure-01.php',
                'expectClosure' => true,
            ],
            [
                'path' => __DIR__ . '/fixtures/load-closure-02.php',
                'expectClosure' => true,
            ],
            [
                'path' => __DIR__ . '/fixtures/load-closure-03.php',
                'expectClosure' => true,
            ],
            [
                'path' => __DIR__ . '/fixtures/load-closure-04.php',
                'expectClosure' => false,
            ],
            [
                'path' => __DIR__ . '/fixtures/load-closure-05.php',
                'expectClosure' => false,
            ],
        ];
    }

    public function testWriteContentsThrowsExceptionWhenFileIsNotAString(): void
    {
        $file = new FileSystemHelper();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File must be a string path or a stream resource; received array.');

        $file->writeContents([], 'string to write');
    }

    public function testWriteContentsThrowsExceptionWhenUnableToWriteToResource(): void
    {
        // Open this test file for reading only.
        $resource = fopen(__FILE__, 'r');
        assert(is_resource($resource));

        $file = new FileSystemHelper();

        $this->expectException(UnableToWriteFileException::class);
        $this->expectExceptionMessage('Unable to write contents to stream resource.');

        $file->writeContents($resource, 'string to write');
    }

    public function testWriteContentsToResource(): void
    {
        $tmpFile = tmpfile();
        assert(is_resource($tmpFile));

        $file = new FileSystemHelper();
        $file->writeContents($tmpFile, 'string to write');

        fseek($tmpFile, 0);

        $this->assertStringContainsString('string to write', (string) fread($tmpFile, 1024));
    }

    public function testWriteContentsThrowsExceptionWhenUnableToWriteToFile(): void
    {
        $file = new FileSystemHelper();

        $this->expectException(UnableToWriteFileException::class);
        $this->expectExceptionMessage('Unable to write contents to file "/path/to/fake/file".');

        $file->writeContents('/path/to/fake/file', 'string to write');
    }

    public function testWriteContentsToFile(): void
    {
        $tmpFile = (string) tempnam(sys_get_temp_dir(), 'formatphp-');
        assert(is_writable($tmpFile));

        $file = new FileSystemHelper();
        $file->writeContents($tmpFile, 'string to write');

        $this->assertStringContainsString('string to write', (string) file_get_contents($tmpFile));

        unlink($tmpFile);
    }

    public function testGetRealPathThrowsExceptionWhenUnableToAccessFile(): void
    {
        $helper = new FileSystemHelper();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to find or access the path at "foo/bar"');

        $helper->getRealPath('foo/bar');
    }

    public function testGetRealPath(): void
    {
        $helper = new FileSystemHelper();

        $this->assertSame(realpath('.'), $helper->getRealPath('.'));
    }

    public function testGetJsonContentsThrowsExceptionWhenUnableToParseJson(): void
    {
        $helper = new FileSystemHelper();

        $this->expectException(UnableToProcessFileException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to decode the JSON in the file "%s"',
            __DIR__ . '/fixtures/get-json-contents-01.json',
        ));

        $helper->getJsonContents(__DIR__ . '/fixtures/get-json-contents-01.json');
    }

    public function testGetJsonContents(): void
    {
        $helper = new FileSystemHelper();

        $this->assertSame(
            ['foo' => 'bar'],
            $helper->getJsonContents(__DIR__ . '/fixtures/get-json-contents-02.json'),
        );
    }

    public function testWriteJsonContents(): void
    {
        $value = [
            'foo' => [
                'bar' => [
                    'baz' => 'qux',
                ],
            ],
        ];

        $expectedContents = <<<'EOD'
            {
                "foo": {
                    "bar": {
                        "baz": "qux"
                    }
                }
            }

            EOD;

        $helper = $this->mockery(FileSystemHelper::class);
        $helper->shouldReceive('writeJsonContents')->passthru();
        $helper->expects()->writeContents('filename.ext', $expectedContents);

        $helper->writeJsonContents('filename.ext', $value);
    }

    public function testWriteJsonContentsThrowsExceptionWhenUnableToEncodeJson(): void
    {
        $helper = new FileSystemHelper();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode contents as JSON');

        $helper->writeJsonContents(__DIR__ . '/foo.json', fopen('php://output', 'r'));
    }
}
