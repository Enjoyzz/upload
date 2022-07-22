<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload\Rule;

use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\Rule\Size;
use GuzzleHttp\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;

class SizeTest extends TestCase
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

    public function testCheckMaxSizeFailed()
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage(
            'File size is too large (9.58 MiB, 10048577 bytes). Must be less than: 1 MiB (1048576 bytes)'
        );

        $file = new UploadedFile(
            $this->tmpFile,
            10_048_577,
            UPLOAD_ERR_OK
        );

        $rule = new Size();
        $rule->setMaxSize(1_048_576);
        $rule->check($file);
    }

    public function testCheckMinSizeFailed()
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage(
            'File size is too small (0 MiB, 0 bytes). Must be greater than or equal to: 1 MiB (1048576 bytes)'
        );

        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK
        );

        $rule = new Size();
        $rule->setMinSize(1_048_576);
        $rule->check($file);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckMaxSizeSuccess()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            9,
            UPLOAD_ERR_OK
        );

        $rule = new Size();
        $rule->setMaxSize(10);
        $rule->check($file);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckMinSizeSuccess()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            2,
            UPLOAD_ERR_OK
        );

        $rule = new Size();
        $rule->setMinSize(1);
        $rule->check($file);
    }

    public function testWithCustomGreaterMessage()
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage(
            'File size > 10 bytes'
        );
        $file = new UploadedFile(
            $this->tmpFile,
            11,
            UPLOAD_ERR_OK
        );

        $rule = new Size('File size > %2$s bytes');
        $rule->setMaxSize(10);
        $rule->check($file);
    }

    public function testWithCustomLessMessage()
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage(
            'File size < 10 bytes'
        );
        $file = new UploadedFile(
            $this->tmpFile,
            9,
            UPLOAD_ERR_OK
        );

        $rule = new Size(errorLessMessage: 'File size < %2$s bytes');
        $rule->setMinSize(10);
        $rule->check($file);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckMaxSizeIfEqualSizes()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            10,
            UPLOAD_ERR_OK
        );

        $rule = new Size();
        $rule->setMaxSize(10);
        $rule->check($file);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckMinSizeIfEqualSizes()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            10,
            UPLOAD_ERR_OK
        );

        $rule = new Size();
        $rule->setMinSize(10);
        $rule->check($file);
    }

}
