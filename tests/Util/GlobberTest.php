<?php

declare(strict_types=1);

namespace FormatPHP\Test\Util;

use FormatPHP\Test\TestCase;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\Globber;

use function array_keys;

class GlobberTest extends TestCase
{
    public function testFind(): void
    {
        $expected = [
            '/path/to/folder/aaa.json',
            '/path/to/folder/foo/bbb.json',
            '/path/to/folder/foo/bar/some.php',
            '/path/to/folder/baz/other.php',
            '/path/to/other/folder/baz/ccc.txt',
        ];

        $pathsFound = [
            '**/*.json' => [
                '/path/to/folder/aaa.json',
                '/path/to/folder/foo',
                '/path/to/folder/foo/bbb.json',
                '/path/to/folder/baz',
                '/path/to/folder/baz/qux/ccc.json',
            ],
            '**/*.php' => [
                '/path/to/folder/foo/bar/some.php',
                '/path/to/folder/baz/other.php',
            ],
            '/path/to/other/folder' => [
                '/path/to/other/folder',
                '/path/to/other/folder/foo',
                '/path/to/other/folder/foo/aaa.txt',
                '/path/to/other/folder/foo/bar/bbb.txt',
                '/path/to/other/folder/baz',
                '/path/to/other/folder/baz/ccc.txt',
            ],
        ];

        $file = $this->mockery(FileSystemHelper::class);

        $file->shouldReceive('getCurrentWorkingDirectory')->andReturn('/path/to/folder/');
        $file->shouldReceive('isDirectory')->with('/path/to/folder/**/*.json')->andReturnFalse();
        $file->shouldReceive('isDirectory')->with('/path/to/folder/**/*.php')->andReturnFalse();
        $file->shouldReceive('isDirectory')->with('/path/to/folder/baz/**/*.json')->andReturnFalse();

        $file->shouldReceive('isDirectory')->with('/path/to/folder/foo')->andReturnTrue();
        $file->shouldReceive('isDirectory')->with('/path/to/folder/baz')->andReturnTrue();
        $file->shouldReceive('isDirectory')->with('/path/to/other/folder')->andReturnTrue();
        $file->shouldReceive('isDirectory')->with('/path/to/other/folder/foo')->andReturnTrue();
        $file->shouldReceive('isDirectory')->with('/path/to/other/folder/baz')->andReturnTrue();

        foreach ($expected as $path) {
            $file->shouldReceive('isDirectory')->with($path)->andReturnFalse();
        }

        $globber = $this->mockery(Globber::class, [$file]);
        $globber->shouldReceive('find')->passthru();

        $globber
            ->shouldReceive('glob')
            ->once()
            ->with('/path/to/folder/**/*.json')
            ->andReturn($pathsFound['**/*.json']);

        $globber
            ->shouldReceive('glob')
            ->once()
            ->with('/path/to/folder/**/*.php')
            ->andReturn($pathsFound['**/*.php']);

        $globber
            ->shouldReceive('glob')
            ->once()
            ->with('/path/to/other/folder/**/*')
            ->andReturn($pathsFound['/path/to/other/folder']);

        $returnedPaths = [];
        foreach ($globber->find(array_keys($pathsFound), ['/path/to/other/folder/foo', 'baz/**/*.json']) as $result) {
            $returnedPaths[] = $result;
        }

        $this->assertEquals($expected, $returnedPaths);
    }

    public function testGlob(): void
    {
        $globber = new Globber($this->mockery(FileSystemHelper::class));

        $this->assertContainsOnly('string', $globber->glob(__DIR__ . '/**/*Test.php'));
    }
}
