<?php

declare(strict_types=1);


namespace Enjoys\Upload;


use Psr\Http\Message\UploadedFileInterface;

/**
 * Contains and processes metadata about an uploaded file
 *
 * Provides methods for handling filename, extension, and media type information
 * with automatic extension preservation when modifying filenames.
 */
final class FileInfo
{
    /**
     * @var string Current filename (may be modified from original)
     */
    private string $filename;

    /**
     * @var string Original filename as provided by client
     */
    private string $originalFilename;

    /**
     * @var string Media type (MIME type) from client upload
     */
    private string $mediaType;

    /**
     * @var string File extension extracted from original filename
     */
    private string $extension;

    /**
     * @var int File size in bytes
     */
    private int $size;

    /**
     * @param UploadedFileInterface $file PSR-7 uploaded file instance
     */
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

    /**
     * Gets filename without extension
     *
     * @return string Filename with extension removed
     */
    public function getFilenameWithoutExtension(): string
    {
        return $this->removeExtension($this->filename);
    }

    /**
     * Gets file extension including leading dot
     *
     * @return string Extension with dot (e.g. ".jpg") or empty string if no extension
     */
    public function getExtensionWithDot(): string
    {
        return (empty($this->extension)) ? '' : '.' . $this->extension;
    }

    /**
     * Sets new filename while preserving original extension
     *
     * @param string $filename New filename (extension will be preserved if not specified)
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $this->removeExtension($filename) . $this->getExtensionWithDot();
    }

    /**
     * Gets current filename (with extension)
     *
     * @return string Current filename including extension
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Gets client-reported media type (MIME type)
     *
     * @return string Media type (e.g. "image/jpeg")
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    /**
     * Gets original client-provided filename
     *
     * @return string Original unchanged filename from upload
     */
    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    /**
     * Gets file extension (without leading dot)
     *
     * @return string File extension in lowercase
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Gets file size in bytes
     *
     * @return int Size in bytes
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Removes current extension from a filename
     *
     * @param string $filename Filename to process
     * @return string Filename with extension removed
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
