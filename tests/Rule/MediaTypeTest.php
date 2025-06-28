<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload\Rule;

use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\Rule\MediaType;
use GuzzleHttp\Psr7\UploadedFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaType::class)]
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


    public function testCheckSuccessIfAllSubTypeIsAllowed()
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

        // Tests without assertions does not generate coverage #3016
        // https://github.com/sebastianbergmann/phpunit/issues/3016
        $this->assertTrue(true);
    }


    public function testCheckSuccessIfAllTypeIsAllowed()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK,
            clientMediaType: 'image/png'
        );

        $rule = new MediaType();
        $rule->allow('*/example');
        $rule->check($file);

        // Tests without assertions does not generate coverage #3016
        // https://github.com/sebastianbergmann/phpunit/issues/3016
        $this->assertTrue(true);
    }


    public function testCheckSuccessIfManyAllowed()
    {
        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK,
            clientMediaType: 'image/png'
        );

        $rule = new MediaType();
        $rule->allow('image/jpg')
        ->allow('image/png');
        $rule->check($file);

        // Tests without assertions does not generate coverage #3016
        // https://github.com/sebastianbergmann/phpunit/issues/3016
        $this->assertTrue(true);
    }

    public function testCheckFailedIfManyAllowedButTypeNotSet()
    {
        $this->expectExceptionMessage('Media type is disallowed: `plain/*`');
        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK,
            clientMediaType: 'plain/jpg'
        );

        $rule = new MediaType();
        $rule->allow('image/jpg')
            ->allow('image/png');
        $rule->check($file);
    }

    public function testCheckFailedIfManyAllowedButTypeAndSubTypeNotSet()
    {
        $this->expectExceptionMessage('Media type is disallowed: `image/svg`');
        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK,
            clientMediaType: 'image/svg'
        );

        $rule = new MediaType();
        $rule->allow('image/jpg')
            ->allow('image/png');
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
            'example' => ['example'],
            'image' => '*',
            'application' => ['json']
        ], $rule->getAllowedMediaType());
    }

    public function testCheckFailedIfMediaTypeIsNull()
    {
        $this->expectExceptionMessage('Media Type ins null');
        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK,
            clientMediaType: null
        );

        $rule = new MediaType();
        $rule->check($file);
    }

    public static function dataForAllowFailed(): array
    {
        return [
            ['image /png'],
            ['image/ png'],
            ['image/'],
            ['/png'],
            ['image'],
            ['*'],
            ['*/'],
            ['/*'],
        ];
    }

    #[DataProvider('dataForAllowFailed')]
    public function testAllowFailed(string $mediaType)
    {
        $this->expectException(RuleException::class);
        $rule = new MediaType();
        $rule->allow($mediaType);
    }


    public function testWithCustomMessage()
    {
        $this->expectException(RuleException::class);
        $this->expectExceptionMessage(
            'The video/avi-mime type is wrong'
        );
        $file = new UploadedFile(
            $this->tmpFile,
            11,
            UPLOAD_ERR_OK,
            clientMediaType: 'video/avi'
        );

        $rule = new MediaType('The %s-mime type is wrong');
        $rule->allow('video/mpeg');
        $rule->check($file);
    }
}
