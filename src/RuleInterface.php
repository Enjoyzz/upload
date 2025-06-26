<?php

declare(strict_types=1);

namespace Enjoys\Upload;

use Enjoys\Upload\Exception\RuleException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Defines contract for file upload validation rules
 *
 * Implement this interface to create custom validation rules
 * that can be used with UploadProcessing
 */
interface RuleInterface
{
    /**
     * Validates an uploaded file against the rule's requirements
     *
     * @param UploadedFileInterface $file The uploaded file to validate
     * @throws RuleException MUST be thrown when validation fails
     *
     * @implementation Note:
     * - MUST return silently on successful validation
     * - MUST throw RuleException with a descriptive message on failure
     * - MUST NOT modify the file contents or stream state
     * - SHOULD validate efficiently without side effects
     */
    public function check(UploadedFileInterface $file): void;
}
