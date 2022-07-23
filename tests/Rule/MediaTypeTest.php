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

    public function testAllowSuccess()
    {
        $rule = new MediaType();
        $rule->allow('example/example');

        $this->assertSame([
            'example' => ['example']
        ], $rule->getAllowedMediaType());

        $rule->allow('image/jpg');
        $this->assertSame([
            'example' => ['example'],
            'image' => ['jpg']
        ], $rule->getAllowedMediaType());

        $rule->allow('application/json');
        $this->assertSame([
            'example' => ['example'],
            'image' => ['jpg'],
            'application' => ['json']
        ], $rule->getAllowedMediaType());

        $rule->allow('image/png ');
        $rule->allow('image/png');
        $this->assertSame([
            'example' => ['example'],
            'image' => ['jpg', 'png'],
            'application' => ['json']
        ], $rule->getAllowedMediaType());

        $rule->allow('image/*');
        $this->assertSame([
            'example' => ['example'],
            'image' => '*',
            'application' => ['json']
        ], $rule->getAllowedMediaType());

        $rule->allow('image/bmp');
        $this->assertSame([
            'example' => ['example'],
            'image' => '*',
            'application' => ['json']
        ], $rule->getAllowedMediaType());

        $rule->allow('image/bmp');
        $this->assertSame([
            'example' => ['example'],
            'image' => '*',
            'application' => ['json']
        ], $rule->getAllowedMediaType());

        $rule->allow('*/bmp');
        $this->assertSame([
            '*' => '*',
        ], $rule->getAllowedMediaType());
    }

    public function dataForAllowFailed()
    {
        return [
            ['image /png'],
            ['image/ png'],
            ['image/'],
            ['/png'],
        ];
    }

    /**
     * @dataProvider dataForAllowFailed
     */
    public function testAllowFailed(string $mediaType)
    {
        $this->expectException(RuleException::class);
        $rule = new MediaType();
        $rule->allow($mediaType);
    }
}
