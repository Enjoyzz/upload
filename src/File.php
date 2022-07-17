<?php

declare(strict_types=1);

namespace Enjoys\Upload;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Http\Message\UploadedFileInterface;

final class File
{
    private string $filename;
    private string $originalFilename;
    private string $mimeType;
    private string $extension;
    private ?int $size;
    private ?string $targetPath = null;

    public function __construct(private UploadedFileInterface $uploadedFile, private FilesystemOperator $filesystem)
    {
        $this->filename = $this->originalFilename = $this->uploadedFile->getClientFilename() ?? '';
        $this->mimeType = $this->uploadedFile->getClientMediaType() ?? '';
        $this->extension = pathinfo(
            $uploadedFile->getClientFilename() ?? '',
            PATHINFO_EXTENSION
        );
        $this->size = $this->uploadedFile->getSize();
    }

    /**
     * @throws FilesystemException
     */
    public function upload(string $targetPath = '/'): void
    {
        $this->targetPath = rtrim($targetPath, '/') . '/' . $this->getFilename();
        $this->filesystem->writeStream($this->targetPath, $this->uploadedFile->getStream()->detach());
    }


    public function setFilename(string $filename): void
    {
        $this->filename = rtrim($filename, $this->getExtensionWithDot()) . $this->getExtensionWithDot();
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFilenameWithoutExtension(): string
    {
        return rtrim($this->getFilename(), $this->getExtensionWithDot());
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getExtensionWithDot(): string
    {
        return (empty($this->getExtension())) ? '' : '.' . $this->getExtension();
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getUploadedFile(): UploadedFileInterface
    {
        return $this->uploadedFile;
    }


    public function getTargetPath(): ?string
    {
        return $this->targetPath;
    }


    public function getFilesystem(): FilesystemOperator
    {
        return $this->filesystem;
    }

}
