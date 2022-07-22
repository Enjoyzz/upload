<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload\Rule;

use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\Rule\MediaType;
use GuzzleHttp\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;

class MediaTypeTest extends TestCase
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

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckSuccess()
    {

        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK,
            clientMediaType: 'image/png'
        );

        $rule = new MediaType();
        $rule->allow('image/*');
        $rule->check($file);
    }
}
