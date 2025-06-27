<?php

declare(strict_types=1);

namespace Enjoys\Upload\Event;

use Enjoys\Upload\UploadProcessing;

/**
 * Event dispatched before file upload processing begins
 *
 * This event allows listeners to modify upload parameters or perform validation
 * before the actual file processing occurs. The upload can be aborted by throwing
 * an exception from an event listener.
 */
final class BeforeUploadEvent extends AbstractUploadEvent
{
    /**
     * @param UploadProcessing $uploadProcessing The upload processing instance containing:
     *        - File metadata (name, size, type)
     *        - Target storage configuration
     *        - Processing options
     *        - Validation rules
     */
    public function __construct(public readonly UploadProcessing $uploadProcessing)
    {
    }
}
