<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload;

use Enjoys\Upload\UploadProcessing;
use GuzzleHttp\Psr7\UploadedFile;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

class UploadProcessingTest extends TestCase
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

    public function testUpload()
    {
        $filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $filesystem);

        $this->assertSame(null, $file->getTargetPath());

        $file->upload('test');
        $this->assertSame('test/original_file_name.txt', $file->getTargetPath());

        $file->upload('test/');
        $this->assertSame('test/original_file_name.txt', $file->getTargetPath());

        $file->upload();
        $this->assertSame('/original_file_name.txt', $file->getTargetPath());
    }

    public function testGetUploadedFile()
    {
        $filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $filesystem);
        $this->assertSame($uploadedFile, $file->getUploadedFile());
    }


    public function testGetFilesystem()
    {
        $filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $filesystem);
        $this->assertSame($filesystem, $file->getFilesystem());
    }

    public function testSetFilename()
    {
        $filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $file = new UploadProcessing($uploadedFile, $filesystem);
        $file->setFilename('test');
        $this->assertSame('test.txt', $file->getFileInfo()->getFilename());
        $this->assertSame('test', $file->getFileInfo()->getFilenameWithoutExtension());
    }
}
