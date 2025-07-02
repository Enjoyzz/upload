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
        $rule->allow('*/png');
        $rule->check($file);
        // Tests without assertions does not generate coverage #3016
        // https://github.com/sebastianbergmann/phpunit/issues/3016
        $this->assertTrue(true);
    }

    #[DataProvider('successTestData')]
    public function testSuccessCheck(string $mediaType, array $allows)
    {
        $file = new UploadedFile(
            $this->tmpFile,
            null,
            UPLOAD_ERR_OK,
            clientMediaType: $mediaType
        );

        $rule = new MediaType();
        foreach ($allows as $allow) {
            $rule->allow($allow);
        }
        $rule->check($file);
        // Tests without assertions does not generate coverage #3016
        // https://github.com/sebastianbergmann/phpunit/issues/3016
        $this->assertTrue(true);
    }

    public static function successTestData(): array
    {
        return [
            ['image/png', ['*/png']],
            ['image/png', ['image/*']],
            ['image/png', ['*/*']],
            ['image/png', ['*/png', 'image/*']],
            ['image/png', ['image/*', '*/png']],
            ['image/png', ['image/*', '*/png', '*/*']],
            ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', ['*/*']],
            ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', ['*/vnd.openxmlformats-officedocument.*']],

        ];
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
        $this->expectExceptionMessage('Media type is disallowed: `plain/jpg`');
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

    public function testCheckFailedIfMediaTypeIsNull()
    {
        $this->expectExceptionMessage('Media type is null');
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
        dump($rule);
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
