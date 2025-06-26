<?php

declare(strict_types=1);

namespace Enjoys\Upload;

use Enjoys\Upload\Exception\RuleException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Interface for file upload validation rules
 *
 * Implement this interface to create custom validation rules
 * that can be used with UploadProcessing
 */
interface RuleInterface
{
    /**
     * Validates an uploaded file according to the rule's requirements
     *
     * @param UploadedFileInterface $file The uploaded file to validate
     * @return void
     * @throws RuleException When validation fails with a descriptive message
     *
     * @note Implementations should:
     *       - Return silently on successful validation
     *       - Throw RuleException with explanation on failure
     *       - Not modify the file contents
     */
    public function check(UploadedFileInterface $file): void;
}
