<?php

declare(strict_types=1);


namespace Enjoys\Upload\Rule;


use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\RuleInterface;
use Psr\Http\Message\UploadedFileInterface;

final class Size implements RuleInterface
{
    private string $errorGreaterMessage;
    private string $errorLessMessage;
    private ?int $minSize = null;
    private ?int $maxSize = null;

    public function __construct(string $errorGreaterMessage = null, string $errorLessMessage = null)
    {
        $this->errorGreaterMessage = $errorGreaterMessage ?? 'File size is too large (%3$s, %4$s bytes). Must be less than: %1$s (%2$s bytes)';
        $this->errorLessMessage = $errorLessMessage ?? 'File size is too small (%3$s, %4$s bytes). Must be greater than or equal to: %1$s (%2$s bytes)';
    }

    public function setMaxSize(int $maxSize): Size
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    public function setMinSize(int $minSize): Size
    {
        $this->minSize = $minSize;
        return $this;
    }

    public function check(UploadedFileInterface $file): void
    {
        $fileSize = $file->getSize() ?? 0;
        if ($this->maxSize !== null) {
            if ($fileSize > $this->maxSize) {
                throw new RuleException(
                    sprintf(
                        $this->errorGreaterMessage,
                        $this->convertBytesToMegaBytes($this->maxSize),
                        $this->maxSize,
                        $this->convertBytesToMegaBytes($fileSize),
                        $fileSize
                    )
                );
            }
        }

        if ($this->minSize !== null) {
            if ($fileSize < $this->minSize) {
                throw new RuleException(
                    sprintf(
                        $this->errorLessMessage,
                        $this->convertBytesToMegaBytes($this->minSize),
                        $this->minSize,
                        $this->convertBytesToMegaBytes($fileSize),
                        $fileSize
                    )
                );
            }
        }
    }

    private function convertBytesToMegaBytes(int $bytes): string
    {
        return round($bytes / pow(1024, 2), 2) . ' MiB';
    }
}
