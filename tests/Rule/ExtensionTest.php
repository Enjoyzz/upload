<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload\Rule;

use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\Rule\Extension;
use GuzzleHttp\Psr7\UploadedFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

#[CoversClass(Extension::class)]
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
            'Files with the png extension are not allowed'
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

    #[DoesNotPerformAssertions]
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

    #[DoesNotPerformAssertions]
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
            'Files with the jpg extension are not allowed'
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

    public function testWithCustomMessage()
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage(
            'The png extension are not allowed'
        );
        $file = new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            clientFilename: $originalFilename = 'file.png'
        );

        $rule = new Extension('The %s extension are not allowed');
        $rule->allow('txt');
        $rule->check($file);
    }

}
