<?php

declare(strict_types=1);

namespace Enjoys\Upload\Event;

use Enjoys\Upload\UploadProcessing;

/**
 * Event dispatched after a file has been successfully uploaded and processed
 *
 * This event provides access to the upload processing instance, allowing listeners
 * to retrieve information about the uploaded file(s), storage details, and any
 * processing results.
 */
final class AfterUploadEvent extends AbstractUploadEvent
{
    /**
     * @param UploadProcessing $uploadProcessing The upload processing instance containing
     *        details about the completed upload operation, including:
     *        - Processed file metadata
     *        - Storage information
     *        - Any transformations applied
     *        - Upload status and results
     */
    public function __construct(public readonly UploadProcessing $uploadProcessing)
    {
    }
}
