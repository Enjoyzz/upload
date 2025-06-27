<?php

declare(strict_types=1);

namespace Enjoys\Upload\Event;

use Enjoys\Upload\UploadProcessing;
use Throwable;

/**
 * Event dispatched when an error occurs during file upload processing
 *
 * This event provides access to both the upload processing instance and the exception
 * that caused the failure, allowing for error handling, logging, or recovery attempts.
 * Common error scenarios include:
 * - File validation failures
 * - Filesystem errors (permissions, quota exceeded)
 * - Processing errors (image manipulation, etc.)
 * - Network errors (for remote storage)
 */
final class UploadErrorEvent extends AbstractUploadEvent
{
    /**
     * @param UploadProcessing $uploadProcessing The upload processing instance containing
     *        details about the failed upload operation
     * @param Throwable $exception The exception that caused the upload to fail
     *        with detailed error information and stack trace
     */
    public function __construct(
        public readonly UploadProcessing $uploadProcessing,
        public readonly Throwable $exception
    ) {
    }
}
