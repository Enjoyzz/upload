<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload;

use Enjoys\Upload\FileInfo;
use GuzzleHttp\Psr7\UploadedFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileInfo::class)]
class FileInfoTest extends TestCase
{
    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'testUpload');
        file_put_contents($this->tmpFile, 'Content');
    }

    public function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testSetFilename()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'file.txt'
        );
        $fileInfo = new FileInfo($file);
        $fileInfo->setFilename('text');
        $this->assertSame($originalFilename, $fileInfo->getOriginalFilename());
        $this->assertSame('text.txt', $fileInfo->getFilename());
    }

    public function testSetFilenameWithoutExtension()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'hosts'
        );
        $fileInfo = new FileInfo($file);
        $fileInfo->setFilename('text');
        $this->assertSame($originalFilename, $fileInfo->getOriginalFilename());
        $this->assertSame('text', $fileInfo->getFilename());
    }

    public function testGetMimeType()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientMediaType: $media_type = 'plain/text'
        );
        $fileInfo = new FileInfo($file);

        $this->assertSame($media_type, $fileInfo->getMediaType());
    }

    public function testGetOriginalFilename()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'file.txt'
        );
        $fileInfo = new FileInfo($file);
        $this->assertSame($originalFilename, $fileInfo->getOriginalFilename());
        $this->assertSame($originalFilename, $fileInfo->getFilename());
    }

    public function testGetExtension()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: 'file.txt'
        );
        $fileInfo = new FileInfo($file);
        $this->assertSame('txt', $fileInfo->getExtension());
    }

    public function testGetExtensionWithDot()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: 'file.txt'
        );
        $fileInfo = new FileInfo($file);
        $this->assertSame('.txt', $fileInfo->getExtensionWithDot());
    }

    public function testGetExtensionWithDotIfFileWithoutExtension()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: 'file'
        );
        $fileInfo = new FileInfo($file);
        $this->assertSame('', $fileInfo->getExtensionWithDot());
    }

    public function testGetSize()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK
        );
        $fileInfo = new FileInfo($file);
        $this->assertSame(128, $fileInfo->getSize());
    }

    public function testGetSizeIfSizeNull()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK
        );
        $fileInfo = new FileInfo($file);
        $this->assertSame(0, $fileInfo->getSize());
    }

    public function testGetFilenameWithoutExtension()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: 'file.txt'
        );
        $fileInfo = new FileInfo($file);
        $this->assertSame('file', $fileInfo->getFilenameWithoutExtension());
        $fileInfo->setFilename('text');
        $this->assertSame('text', $fileInfo->getFilenameWithoutExtension());
    }

    public function testSetFilenameWithNonStandardExtension()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'test.^extension'
        );
        $fileInfo = new FileInfo($file);
        $fileInfo->setFilename('text.^extension');
        $this->assertSame($originalFilename, $fileInfo->getOriginalFilename());
        $this->assertSame('text.^extension', $fileInfo->getFilename());
        $this->assertSame('text', $fileInfo->getFilenameWithoutExtension());
    }
}
