<?php

declare(strict_types=1);

namespace Enjoys\Tests\Upload;

use Enjoys\Upload\Event\AbstractUploadEvent;
use Enjoys\Upload\Event\AfterUploadEvent;
use Enjoys\Upload\Event\BeforeUploadEvent;
use Enjoys\Upload\Event\BeforeValidationEvent;
use Enjoys\Upload\Event\UploadErrorEvent;
use Enjoys\Upload\FileInfo;
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
#[CoversClass(FileInfo::class)]
#[CoversClass(BeforeValidationEvent::class)]
#[CoversClass(BeforeUploadEvent::class)]
#[CoversClass(AfterUploadEvent::class)]
#[CoversClass(UploadErrorEvent::class)]
final class UploadProcessingEventTest extends TestCase
{
    private string $tmpFile;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'testUpload');
        file_put_contents($this->tmpFile, 'Content');
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
    }

    protected function tearDown(): void
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
        $uploadedFile = $this->createUploadedFile();
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatchedEvents = [];
        $dispatcher->method('dispatch')
            ->willReturnCallback(function ($event) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $event;
                return $event;
            });

        $upload = new UploadProcessing($uploadedFile, $this->filesystem, $dispatcher);
        $upload->upload();

        $this->assertCount(3, $dispatchedEvents);
        $this->assertInstanceOf(BeforeValidationEvent::class, $dispatchedEvents[0]);
        $this->assertInstanceOf(BeforeUploadEvent::class, $dispatchedEvents[1]);
        $this->assertInstanceOf(AfterUploadEvent::class, $dispatchedEvents[2]);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function testErrorEventIsDispatchedOnException(): void
    {
        $this->expectException(RuntimeException::class);
        $uploadedFile = $this->createUploadedFile();
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('writeStream')
            ->willThrowException(new RuntimeException('Test error'));

        $dispatchedEvents = [];
        $dispatcher->method('dispatch')
            ->willReturnCallback(function ($event) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $event;
                return $event;
            });

        $upload = new UploadProcessing($uploadedFile, $filesystem, $dispatcher);

        try {
            $upload->upload();
            $this->fail('Expected exception was not thrown');
        } catch (\Throwable $e) {
            $this->assertSame('Test error', $e->getMessage());

            $this->assertCount(3, $dispatchedEvents);
            $this->assertInstanceOf(BeforeValidationEvent::class, $dispatchedEvents[0]);
            $this->assertInstanceOf(BeforeUploadEvent::class, $dispatchedEvents[1]);
            $this->assertInstanceOf(UploadErrorEvent::class, $dispatchedEvents[2]);

            /** @var UploadErrorEvent $errorEvent */
            $errorEvent = $dispatchedEvents[2];
            $this->assertSame($upload, $errorEvent->uploadProcessing);
            $this->assertSame($e, $errorEvent->exception);
            throw $e;
        }
    }

    /**
     * @throws FilesystemException
     * @throws Throwable
     */
    public function testWorksWithoutDispatcher(): void
    {
        $uploadedFile = $this->createUploadedFile();
        $upload = new UploadProcessing($uploadedFile, $this->filesystem);
        $upload->upload();

        $this->assertNotNull($upload->getTargetPath());
        $this->assertTrue($this->filesystem->fileExists($upload->getTargetPath()));
    }

    /**
     * @throws FilesystemException
     * @throws Throwable
     */
    public function testEventContainsCorrectContext(): void
    {
        $uploadedFile = $this->createUploadedFile();
        $dispatcher = new class () implements EventDispatcherInterface {
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

        $beforeValidationEvent = $dispatcher->dispatchedEvents[0];
        $this->assertSame($upload, $beforeValidationEvent->uploadProcessing);

        $beforeUploadEvent = $dispatcher->dispatchedEvents[1];
        $this->assertSame($upload, $beforeUploadEvent->uploadProcessing);

        $afterUploadEvent = $dispatcher->dispatchedEvents[2];
        $this->assertSame('/test/path/test.txt', $afterUploadEvent->uploadProcessing->getTargetPath());
    }

    private function createUploadedFile(?string $clientFilename = null, ?string $mediaType = null): UploadedFile
    {
        return new UploadedFile(
            $this->tmpFile,
            128,
            UPLOAD_ERR_OK,
            $clientFilename ?? 'original_file_name.txt',
            $mediaType ?? 'plain/text'
        );
    }

    public function testEventPropagation(): void
    {
        $event = new class () extends AbstractUploadEvent {};
        $this->assertFalse($event->isPropagationStopped());
        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testEventPropagationStopping(): void
    {

        $dispatcher = new class () implements EventDispatcherInterface {
            public array $dispatchedEvents = [];

            public function dispatch(object $event): object
            {
                if ($event->isPropagationStopped()) {
                    return $event;
                }

                $this->dispatchedEvents[] = $event;
                return $event;
            }
        };

        $uploadProcessing = new UploadProcessing($this->createUploadedFile(), $this->filesystem, $dispatcher);

        $event1 = new AfterUploadEvent($uploadProcessing);
        $event2 = new AfterUploadEvent($uploadProcessing);

        $event1->stopPropagation();

        $dispatcher->dispatch($event1);
        $dispatcher->dispatch($event2);

        $this->assertCount(1, $dispatcher->dispatchedEvents);
        $this->assertSame($event2, $dispatcher->dispatchedEvents[0]);
    }
}
