<?php

declare(strict_types=1);

namespace Enjoys\Upload\Rule;

use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\RuleInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Validates uploaded files against allowed media (MIME) types
 *
 * This rule checks if the file's media type matches the configured allowed patterns.
 * Supports three types of patterns:
 * 1. Exact matches: "type/subtype" (e.g. "image/png")
 * 2. Type wildcards: "type/*" (e.g. "image/*" allows all image types)
 * 3. Subtype wildcards: "*\/subtype" (e.g. "*\/png" allows png in any type)
 *
 * Note:
 * - The special pattern "*" is supported and automatically converted to "*\/*"
 * - Patterns must strictly follow "type/subtype" format:
 *   - No spaces around slash
 *   - No missing parts
 *   - Exactly one slash separating type and subtype
 * - Validation is case-sensitive
 */
final class MediaType implements RuleInterface
{
    /**
     * @var string[] Array of compiled regex patterns
     * @psalm-var non-empty-string[]
     */
    private array $allowPatterns = [];

    /**
     * @var string Error message template (uses %s placeholder for invalid type)
     */
    private string $errorMessage;

    /**
     * @param string|null $errorMessage Custom error message when validation fails.
     *        The %s placeholder will be replaced with the rejected media type.
     *        Default: "Media type is disallowed: `%s`"
     */
    public function __construct(string $errorMessage = null)
    {
        $this->errorMessage = $errorMessage ?? 'Media type is disallowed: `%s`';
    }

    /**
     * Validates the uploaded file's media type
     *
     * @param UploadedFileInterface $file Uploaded file to validate
     * @throws RuleException When media type is invalid or not allowed
     */
    #[\Override]
    public function check(UploadedFileInterface $file): void
    {
        $mediaType = $file->getClientMediaType() ?? throw new RuleException('Media type is null');

        foreach ($this->allowPatterns as $pattern) {
            if (preg_match($pattern, $mediaType)) {
                return;
            }
        }

        throw new RuleException(sprintf($this->errorMessage, $mediaType));
    }

    /**
     * Adds an allowed media type pattern
     *
     * Supported patterns:
     * - Specific type/subtype ("image/png")
     * - All subtypes for type ("image/*")
     * - Specific subtype across all types ("*\/png")
     *
     * @param string $pattern Media type pattern to allow. Must contain exactly one '/'
     *        with no surrounding spaces. Examples:
     *        - Valid: "*", "image/jpeg", "image/*", "*\/png"
     *        - Invalid: "image/", "/png", "image /*", "image/ png"
     *
     * @return self For method chaining
     * @throws RuleException When:
     *         - Pattern doesn't contain '/'
     *         - Has leading/trailing spaces around '/'
     *         - Missing type, or subtype part
     *         - Uses standalone "*" (use "image/*")
     *
     * @see MediaTypeTest::dataForAllowFailed() For all invalid cases
     */
    public function allow(string $pattern): MediaType
    {
        if ($pattern === '*') {
            $pattern = '*/*';
        }
        $this->validateMediaTypePattern($pattern);
        $this->allowPatterns[] = $this->createRegexPattern($pattern);
        return $this;
    }

    /**
     * @psalm-return non-empty-string
     */
    private function createRegexPattern(string $string): string
    {
        $escaped = preg_quote($string, '/');
        $regex = str_replace('\*', '.*', $escaped);
        return '/^' . $regex . '$/i';
    }


    public function validateMediaTypePattern(string $pattern): void
    {
        if (substr_count($pattern, '/') !== 1) {
            throw new RuleException(sprintf(
                'Media type pattern must contain exactly one "/": %s',
                $pattern
            ));
        }

        [$type, $subType] = explode('/', $pattern);

        if ($type === '' || $subType === '') {
            throw new RuleException(sprintf(
                'Media type pattern parts cannot be empty: %s',
                $pattern
            ));
        }

        if (str_contains($type, ' ') || str_contains($subType, ' ')) {
            throw new RuleException(sprintf(
                'Media type pattern contains spaces: %s',
                $pattern
            ));
        }
    }

}
