<?php

declare(strict_types=1);


namespace Enjoys\Upload\Rule;


use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\RuleInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Validates uploaded file size against minimum and maximum limits
 *
 * This rule checks if the file size falls within specified boundaries.
 * Both min and max limits are optional but at least one must be set.
 * Size comparisons are done in bytes but error messages show both bytes and MiB.
 */
final class Size implements RuleInterface
{
    /**
     * @var string Error message template when file exceeds maximum size
     *             Placeholders: %1$s - max size in MiB, %2$s - max size in bytes,
     *                           %3$s - actual size in MiB, %4$s - actual size in bytes
     */
    private string $errorGreaterMessage;

    /**
     * @var string Error message template when file is below minimum size
     *             Placeholders: %1$s - min size in MiB, %2$s - min size in bytes,
     *                           %3$s - actual size in MiB, %4$s - actual size in bytes
     */
    private string $errorLessMessage;

    /**
     * @var int|null Minimum allowed file size in bytes (null means no minimum)
     */
    private ?int $minSize = null;

    /**
     * @var int|null Maximum allowed file size in bytes (null means no maximum)
     */
    private ?int $maxSize = null;

    /**
     * @param string|null $errorGreaterMessage Custom error message when the file exceeds maximum size.
     *        The message supports these placeholders:
     *        - %1$s: Maximum allowed size in MiB (e.g. "2.5 MiB")
     *        - %2$s: Maximum allowed size in bytes (e.g. "2621440")
     *        - %3$s: Actual file size in MiB
     *        - %4$s: Actual file size in bytes
     *        Default: "File size is too large (%3$s, %4$s bytes). Must be less than: %1$s (%2$s bytes)"
     *
     * @param string|null $errorLessMessage Custom error message when the file is smaller than the minimum size.
     *        Uses the same placeholders as $errorGreaterMessage.
     *        Default: "File size is too small (%3$s, %4$s bytes). Must be greater than or equal to: %1$s (%2$s bytes)"
     */
    public function __construct(string $errorGreaterMessage = null, string $errorLessMessage = null)
    {
        $this->errorGreaterMessage = $errorGreaterMessage ?? 'File size is too large (%3$s, %4$s bytes). Must be less than: %1$s (%2$s bytes)';
        $this->errorLessMessage = $errorLessMessage ?? 'File size is too small (%3$s, %4$s bytes). Must be greater than or equal to: %1$s (%2$s bytes)';
    }

    /**
     * Sets maximum allowed file size
     *
     * @param int $maxSize Maximum size in bytes
     * @return self For method chaining
     */
    public function setMaxSize(int $maxSize): Size
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    /**
     * Sets minimum allowed file size
     *
     * @param int $minSize Minimum size in bytes
     * @return self For method chaining
     */
    public function setMinSize(int $minSize): Size
    {
        $this->minSize = $minSize;
        return $this;
    }

    /**
     * Performs the size validation check
     *
     * @param UploadedFileInterface $file Uploaded file to validate
     * @throws RuleException When file size violates min/max constraints
     */
    #[\Override]
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

    /**
     * Converts bytes to human-readable megabytes (MiB)
     *
     * @param int $bytes Size in bytes
     * @return string Formatted size in MiB (e.g. "3.25 MiB")
     */
    private function convertBytesToMegaBytes(int $bytes): string
    {
        return ((string)round((float)$bytes / pow(1024, 2), 2)) . ' MiB';
    }
}
