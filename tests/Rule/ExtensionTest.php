<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload\Rule;

use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\Rule\Extension;
use GuzzleHttp\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;

class ExtensionTest extends TestCase
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

    public function testCheckFailed()
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage(
            'Загрузка файлов с расширением png запрещена'
        );
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'file.png'
        );

        $rule = new Extension();
        $rule->allow('txt');
        $rule->check($file);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckSuccess()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'file.TXT'
        );

        $rule = new Extension();
        $rule->allow('txt');
        $rule->check($file);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckSuccessWithManyAllowed()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'file.jpeg'
        );

        $rule = new Extension();
        $rule->allow('JPG, PNG, JPEG');
        $rule->check($file);
    }

    public function testCheckFailedWithManyAllowed()
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage(
            'Загрузка файлов с расширением jpg запрещена'
        );
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'file.JPG'
        );

        $rule = new Extension();
        $rule->allow([
            'jpeg',
            'png'
        ]);
        $rule->check($file);
    }

}
