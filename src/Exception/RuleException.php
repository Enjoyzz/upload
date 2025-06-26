<?php

declare(strict_types=1);

namespace Enjoys\Upload\Exception;

/**
 * Exception thrown when a validation rule fails during file upload processing
 *
 * RuleInterface implementations must throw this exception
 * when file validation against their rules fails.
 *
 * @see RuleInterface
 */
final class RuleException extends \RuntimeException
{
}
