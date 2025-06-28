<?php

declare(strict_types=1);

namespace Enjoys\Upload;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;

final class UploadProcessing
{

    /**
     * @var string|null Final storage path (null until file is uploaded)
     */
    private ?string $targetPath = null;

    /**
     * @var FileInfo Contains processed file metadata and name handling
     */
    private FileInfo $fileInfo;

    /**
     * @var RuleInterface[] Array of validation rules
     */
    private array $rules = [];

    /**
     * @param UploadedFileInterface $uploadedFile The PSR-7 uploaded file to process
     * @param Filesystem $filesystem Flysystem instance that provides filesystem abstraction
     * (supports local, FTP, S3, and other storage systems)
     */
    public function __construct(
        private readonly UploadedFileInterface $uploadedFile,
        private readonly Filesystem $filesystem,
    ) {
        $this->fileInfo = new FileInfo($uploadedFile);
    }

    /**
     * Uploads the file to the target filesystem
     *
     * @param string $targetPath The target directory path (defaults to '/')
     * @throws FilesystemException If there's an error during file system operations
     */
    public function upload(string $targetPath = '/'): void
    {
        $this->validate();

        $this->targetPath = rtrim($targetPath, '/') . '/' . $this->fileInfo->getFilename();

        $resource = $this->uploadedFile->getStream()->detach();
        try {
            $this->filesystem->writeStream($this->targetPath, $resource);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    /**
     * Validates the uploaded file against all registered rules
     */
    private function validate(): void
    {
        foreach ($this->rules as $rule) {
            $rule->check($this->getUploadedFile());
        }
    }

    /**
     * Sets the filename for the uploaded file
     * Automatically adds an extension if it is not specified
     *
     * @param string $filename The desired filename
     */
    public function setFilename(string $filename): void
    {
        $this->fileInfo->setFilename($filename);
    }

    /**
     * Returns the PSR-7 UploadedFileInterface instance representing the uploaded file
     *
     * @return UploadedFileInterface UploadedFileInterface The PSR-7 compliant uploaded file object
     */
    public function getUploadedFile(): UploadedFileInterface
    {
        return $this->uploadedFile;
    }

    /**
     * Gets the target path where the file was/will be uploaded
     *
     * @return string|null The target path or null if not uploaded yet
     */
    public function getTargetPath(): ?string
    {
        return $this->targetPath;
    }

    /**
     * Gets the filesystem instance used for storage
     *
     * @return Filesystem The filesystem instance
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Gets the file information object
     *
     * @return FileInfo The file information instance
     */
    public function getFileInfo(): FileInfo
    {
        return $this->fileInfo;
    }

    /**
     * Adds a single validation rule
     *
     * @param RuleInterface $rule The rule to add
     */
    public function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * Adds multiple validation rules at once
     *
     * @param RuleInterface[] $rules Array of rules to add
     */
    public function addRules(array $rules): void
    {
        $this->rules = array_merge($this->rules, $rules);
    }

    /**
     * Gets all registered validation rules
     *
     * @return RuleInterface[] Array of validation rules
     */
    public function getRules(): array
    {
        return $this->rules;
    }


}
