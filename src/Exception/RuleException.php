<?php

declare(strict_types=1);

namespace Enjoys\Upload\Exception;

/**
 * Exception thrown when a validation rule fails during file upload processing
 *
 * This exception should be thrown by RuleInterface implementations
 * when uploaded files fail validation checks.
 */
final class RuleException extends \RuntimeException
{
    /**
     * @param string $message Description of the validation failure
     * @param int $code Exception code (default: 0)
     * @param \Throwable|null $previous Previous exception for chaining
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
