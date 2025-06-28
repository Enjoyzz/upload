<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload;

use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\FileInfo;
use Enjoys\Upload\RuleInterface;
use Enjoys\Upload\UploadProcessing;
use GuzzleHttp\Psr7\UploadedFile;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

#[CoversClass(UploadProcessing::class)]
#[CoversClass(FileInfo::class)]
class UploadProcessingTest extends TestCase
{
    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'testUpload');
        file_put_contents($this->tmpFile, 'Content');
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
    }

    public function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }


    /**
     * @throws FilesystemException
     */
    public function testUploadInSubDirectory()
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $file->upload('memory');
        $this->assertSame('memory/original_file_name.txt', $file->getTargetPath());
        $this->assertSame('Content', $this->filesystem->read($file->getTargetPath()));
    }

    /**
     * @throws FilesystemException
     */
    public function testUploadInRootDirectory()
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $file->upload();
        $this->assertSame('/original_file_name.txt', $file->getTargetPath());
        $this->assertSame('Content', $this->filesystem->read($file->getTargetPath()));
    }


    /**
     * @throws FilesystemException
     * @throws Exception
     */
    public function testUploadWithValidateSuccess()
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->expects($this->once())->method('check');
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK);
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $file->addRule($rule);
        $file->upload();
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    public function testUploadWithValidateFailed()
    {
        $this->expectExceptionMessage($errorMessage = 'error');
        $this->expectException(RuleException::class);

        $rule = $this->createMock(RuleInterface::class);
        $rule->expects($this->once())->method('check')->willThrowException(new RuleException($errorMessage));
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK);
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $file->addRule($rule);

        $file->upload();
    }

    public function testGetUploadedFile()
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $this->assertSame($uploadedFile, $file->getUploadedFile());
    }


    public function testGetFilesystem()
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $this->assertSame($this->filesystem, $file->getFilesystem());
    }

    public function testSetFilename()
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $file->setFilename('test');
        $this->assertSame('test.txt', $file->getFileInfo()->getFilename());
        $this->assertSame('test', $file->getFileInfo()->getFilenameWithoutExtension());
    }

    /**
     * @throws Exception
     */
    public function testAddRule()
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $file->addRule($this->createMock(RuleInterface::class));
        $this->assertCount(1, $file->getRules());
    }

    /**
     * @throws Exception
     */
    public function testAddRules(): void
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $this->filesystem);
        $rules = array_fill(0, 3, $this->createMock(RuleInterface::class));
        $file->addRule($this->createMock(RuleInterface::class));
        $file->addRules($rules);
        $this->assertCount(4, $file->getRules());
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    public function testWriteStreamClosesResource(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $resource = fopen('php://memory', 'r+');

        $stream->method('detach')
            ->willReturn($resource);

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->method('getStream')
            ->willReturn($stream);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('writeStream')
            ->with('/target/path/', $resource);

        $upload = new UploadProcessing($uploadedFile, $filesystem);
        $upload->upload('/target/path');

        $this->assertFalse(is_resource($resource), 'Resource should be closed');
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    public function testClosesResourceOnWriteFailure(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $resource = fopen('php://memory', 'r+');

        $stream->method('detach')
            ->willReturn($resource);

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->method('getStream')
            ->willReturn($stream);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('writeStream')
            ->willThrowException(new \RuntimeException('Write error'));

        $upload = new UploadProcessing($uploadedFile, $filesystem);


        try {
            $upload->upload();
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertFalse(is_resource($resource), 'Resource should be closed even on failure');
        }
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    public function testHandlesAlreadyClosedResource(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $resource = fopen('php://memory', 'r+');
        fclose($resource);

        $stream->method('detach')
            ->willReturn($resource);

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->method('getStream')
            ->willReturn($stream);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('writeStream')
            ->with('/target/path/', $resource);

        $upload = new UploadProcessing($uploadedFile, $filesystem);
        $upload->upload('/target/path');
    }
}
