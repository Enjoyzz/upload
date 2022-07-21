<?php

declare(strict_types=1);


namespace Enjoys\Upload;


use Psr\Http\Message\UploadedFileInterface;

final class FileInfo
{
    private string $filename;
    private string $originalFilename;
    private string $mimeType;
    private string $extension;
    private int $size;

    public function __construct(UploadedFileInterface $file)
    {
        $this->filename = $this->originalFilename = $file->getClientFilename() ?? '';

        $this->mimeType = $file->getClientMediaType() ?? '';

        $this->extension = pathinfo(
            $file->getClientFilename() ?? '',
            PATHINFO_EXTENSION
        );

        $this->size = $file->getSize() ?? 0;
    }

    public function getFilenameWithoutExtension(): string
    {
        return rtrim($this->filename, $this->getExtensionWithDot());
    }


    public function getExtensionWithDot(): string
    {
        return (empty($this->extension)) ? '' : '.' . $this->extension;
    }

    /**
     * Automatically adds an extension if it is not specified
     * @param string $filename
     * @return void
     */
    public function setFilename(string $filename): void
    {
        $this->filename = rtrim($filename, $this->getExtensionWithDot()) . $this->getExtensionWithDot();
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }


    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getSize(): int
    {
        return $this->size;
    }


}
