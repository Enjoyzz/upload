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

    private bool $validate = true;

    /**
     * @var RuleInterface[]
     */
    private array $rules = [];

    public function __construct(private UploadedFileInterface $uploadedFile, private Filesystem $filesystem)
    {
        $this->fileInfo = new FileInfo($uploadedFile);
    }

    /**
     * @throws FilesystemException
     */
    public function upload(string $targetPath = '/'): void
    {
        if ($this->validate){
            $this->validate();
        }

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


    public function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * @param RuleInterface[] $rules
     * @return void
     */
    public function addRules(array $rules): void
    {
        $this->rules[] = array_merge($this->rules, $rules);
    }

    private function validate(): void
    {
        foreach ($this->rules as $rule) {
            $rule->check($this->getUploadedFile());
        }
    }

    public function isValidate(): bool
    {
        return $this->validate;
    }

    public function setValidate(bool $validate): void
    {
        $this->validate = $validate;
    }


}
