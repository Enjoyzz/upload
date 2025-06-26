<?php

declare(strict_types=1);


namespace Enjoys\Upload;


use Psr\Http\Message\UploadedFileInterface;

final class FileInfo
{
    private string $filename;
    private string $originalFilename;
    private string $mediaType;
    private string $extension;
    private int $size;

    public function __construct(UploadedFileInterface $file)
    {
        $this->filename = $this->originalFilename = $file->getClientFilename() ?? '';

        $this->mediaType = $file->getClientMediaType() ?? '';

        $this->extension = pathinfo(
            $file->getClientFilename() ?? '',
            PATHINFO_EXTENSION
        );

        $this->size = $file->getSize() ?? 0;
    }

    public function getFilenameWithoutExtension(): string
    {
        return $this->removeExtension($this->filename);
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
        $this->filename = $this->removeExtension($filename) . $this->getExtensionWithDot();
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
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

    /**
     * @param string $filename
     * @return string
     */
    private function removeExtension(string $filename): string
    {
        return preg_replace(
            sprintf('/%s$/', preg_quote($this->getExtensionWithDot())),
            '',
            $filename
        ) ?? '';
    }


}
