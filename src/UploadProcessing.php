<?php

declare(strict_types=1);

namespace Enjoys\Upload;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;

final class UploadProcessing
{

    private ?string $targetPath = null;
    private FileInfo $fileInfo;

    public function __construct(private UploadedFileInterface $uploadedFile, private Filesystem $filesystem)
    {
        $this->fileInfo = new FileInfo($uploadedFile);
    }

    /**
     * @throws FilesystemException
     */
    public function upload(string $targetPath = '/'): void
    {
        $this->targetPath = rtrim($targetPath, '/') . '/' . $this->fileInfo->getFilename();
        $this->filesystem->writeStream($this->targetPath, $this->uploadedFile->getStream()->detach());
    }

    /**
     * Automatically adds an extension if it is not specified
     * @param string $filename
     * @return void
     */
    public function setFilename(string $filename): void
    {
        $this->fileInfo->setFilename($filename);
    }


    public function getUploadedFile(): UploadedFileInterface
    {
        return $this->uploadedFile;
    }


    public function getTargetPath(): ?string
    {
        return $this->targetPath;
    }


    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * @return FileInfo
     */
    public function getFileInfo(): FileInfo
    {
        return $this->fileInfo;
    }

}
