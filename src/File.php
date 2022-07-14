<?php

declare(strict_types=1);

namespace Enjoys\Upload;

use Psr\Http\Message\UploadedFileInterface;

final class File
{


    private ?string $filename;
    private ?string $originalFilename;
    private ?string $mimeType;
    private string $extension;
    private ?int $size;

    public function __construct(private UploadedFileInterface $uploadedFile, private StorageInterface $storage)
    {
        $this->filename = $this->uploadedFile->getClientFilename();
        $this->originalFilename = $this->uploadedFile->getClientFilename();
        $this->mimeType = $this->uploadedFile->getClientMediaType();
        $this->extension = pathinfo(
            $uploadedFile->getClientFilename(),
            PATHINFO_EXTENSION
        );
        $this->size = $this->uploadedFile->getSize();
    }

    public function upload()
    {
        return $this->storage->upload($this);
    }


    public function setFilename(string $filename): void
    {
        $extension = (empty($this->getExtension())) ? '' : '.' . $this->getExtension();
        $this->filename = rtrim($filename, $extension) . $extension;
    }

    public function getFilenameWithoutExtension(): string
    {
        $extension = (empty($this->getExtension())) ? '' : '.' . $this->getExtension();
        return rtrim($this->getFilename(), $extension);
    }

    /**
     * @return UploadedFileInterface
     */
    public function getUploadedFile(): UploadedFileInterface
    {
        return $this->uploadedFile;
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @return string|null
     */
    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }


}
