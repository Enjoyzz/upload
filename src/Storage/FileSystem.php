<?php

declare(strict_types=1);

namespace Enjoys\Upload\Storage;

use Enjoys\Upload\File;
use Enjoys\Upload\StorageInterface;

use function Enjoys\FileSystem\createDirectory;

final class FileSystem implements StorageInterface
{
    private string $directory;

    /**
     * @throws \Exception
     */
    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, '/') . '/';
        createDirectory($this->directory);
    }

    public function upload(File $file): ?string
    {
        $targetPath = $this->directory . $file->getFilename();
        $file->getUploadedFile()->moveTo($targetPath);
        return $targetPath;
    }
}
