<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload;

use Enjoys\Upload\Event\AfterUploadEvent;
use Enjoys\Upload\Event\BeforeUploadEvent;
use Enjoys\Upload\Event\BeforeValidationEvent;
use Enjoys\Upload\Event\UploadErrorEvent;
use Enjoys\Upload\UploadProcessing;
use GuzzleHttp\Psr7\UploadedFile;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Throwable;

#[CoversClass(UploadProcessing::class)]
class UploadProcessingEventTest extends TestCase
{
    private string $tmpFile;
    private Filesystem $filesystem;

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
     * @throws Throwable
     * @throws Exception
     */
    public function testEventsAreDispatchedInCorrectOrder(): void
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatchedEvents = [];
        $dispatcher->method('dispatch')
            ->willReturnCallback(function ($event) use (&$dispatchedEvents) {
                $dispatchedEvents[] = get_class($event);
                return $event;
            });

        $upload = new UploadProcessing($uploadedFile, $this->filesystem, $dispatcher);
        $upload->upload();

        $this->assertSame([
            BeforeValidationEvent::class,
            BeforeUploadEvent::class,
            AfterUploadEvent::class
        ], $dispatchedEvents);
    }

    /**
     * @throws FilesystemException
     * @throws Throwable
     * @throws Exception
     */
    public function testErrorEventIsDispatchedOnException(): void
    {
        $this->expectException(RuntimeException::class);
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('writeStream')->willThrowException(
            new RuntimeException('Test error')
        );

        $dispatchedEvents = [];
        $dispatcher->method('dispatch')
            ->willReturnCallback(function ($event) use (&$dispatchedEvents) {
                $dispatchedEvents[] = get_class($event);
                return $event;
            });

        $upload = new UploadProcessing($uploadedFile, $filesystem, $dispatcher);
        try {
            $upload->upload();
            $this->fail('Expected exception was not thrown');
        } catch (Throwable $e) {
            $this->assertSame('Test error', $e->getMessage());
            $this->assertCount(3, $dispatchedEvents);
            $this->assertSame([
                BeforeValidationEvent::class,
                BeforeUploadEvent::class,
                UploadErrorEvent::class
            ], $dispatchedEvents);
            throw $e;
        }
    }

    /**
     * @throws FilesystemException
     * @throws Throwable
     */
    public function testWorksWithoutDispatcher(): void
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $upload = new UploadProcessing($uploadedFile, $this->filesystem);
        $upload->upload();
        $this->assertNotNull($upload->getTargetPath());
    }

    /**
     * @throws FilesystemException
     * @throws Throwable
     */
    public function testEventContainsCorrectContext(): void
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 128, UPLOAD_ERR_OK, 'original_file_name.txt', 'plain/text');
        $dispatcher = new class implements EventDispatcherInterface {

            public array $dispatchedEvents = [];

            public function dispatch(object $event): object
            {
                $this->dispatchedEvents[] = $event;
                return $event;
            }
        };

        $upload = new UploadProcessing($uploadedFile, $this->filesystem, $dispatcher);
        $upload->setFilename('test.txt');
        $upload->upload('/test/path');

        $this->assertCount(3, $dispatcher->dispatchedEvents);

        /** @var BeforeValidationEvent $beforeValidationEvent */
        $beforeValidationEvent = $dispatcher->dispatchedEvents[0];
        $this->assertSame($upload, $beforeValidationEvent->uploadProcessing);

        /** @var BeforeUploadEvent $beforeUploadEvent */
        $beforeUploadEvent = $dispatcher->dispatchedEvents[1];
        $this->assertSame($upload, $beforeUploadEvent->uploadProcessing);

        /** @var AfterUploadEvent $afterUploadEvent */
        $afterUploadEvent = $dispatcher->dispatchedEvents[2];
        $this->assertSame('/test/path/test.txt', $afterUploadEvent->uploadProcessing->getTargetPath());
    }
}
