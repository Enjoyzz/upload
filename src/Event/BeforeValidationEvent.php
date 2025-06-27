<?php

declare(strict_types=1);

namespace Enjoys\Upload\Event;

use Enjoys\Upload\UploadProcessing;

/**
 * Event dispatched before file validation occurs
 *
 * This event allows listeners to modify validation rules or perform
 * custom pre-validation checks before the standard validation process.
 * The upload can be aborted by throwing an exception from an event listener.
 */
final class BeforeValidationEvent extends AbstractUploadEvent
{
    /**
     * @param UploadProcessing $uploadProcessing The upload processing instance containing:
     *        - File metadata (name, size, temporary path)
     *        - Current validation rules
     *        - Upload configuration
     *        - User-defined validation callbacks
     */
    public function __construct(public readonly UploadProcessing $uploadProcessing)
    {
    }
}
