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
 * - The special pattern "*" is NOT supported (will throw RuleException)
 * - Patterns must strictly follow "type/subtype" format:
 *   - No spaces around slash
 *   - No missing parts
 *   - No standalone wildcards
 * - Validation is case-sensitive
 */
final class MediaType implements RuleInterface
{

    /**
     * @var array Allowed media types in format:
     *   [
     *     'type' => ['subtype1', 'subtype2'], // Specific subtypes
     *     'type' => '*',                      // All subtypes for type
     *   ]
     *   or empty array if none allowed
     */
    private array $allowedMediaType = [];

    /**
     * @var string Error message template (uses %s placeholder for invalid type)
     */
    private string $errorMessage;

    /**
     * @var bool Flag to allow all media types (when '*' is set as type)
     */
    private bool $allowedAllMediaType = false;

    /**
     * @param string|null $errorMessage Custom error message when validation fails.
     *        The %s placeholder will be replaced with the rejected media type.
     *        Default: "Media type is disallow: `%s`"
     */
    public function __construct(string $errorMessage = null)
    {
        $this->errorMessage = $errorMessage ?? 'Media type is disallow: `%s`';
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
        $mediaType = $file->getClientMediaType() ?? throw new RuleException('Media Type ins null');

        if ($this->allowedAllMediaType) {
            return;
        }

        list($type, $subType) = $this->explode($mediaType);

        if (!array_key_exists($type, $this->allowedMediaType)) {
            throw new RuleException(sprintf($this->errorMessage, sprintf('%s/*', $type)));
        }

        /** @var string|string[] $allowed */
        $allowed = $this->allowedMediaType[$type];

        if ($allowed === '*') {
            return;
        }

        /** @var string[] $allowed */
        if (!in_array($subType, $allowed, true)) {
            throw new RuleException(sprintf($this->errorMessage, $mediaType));
        }
    }

    /**
     * Adds an allowed media type pattern
     *
     * Supported patterns:
     * - Specific type/subtype ("image/png")
     * - All subtypes for type ("image/*")
     * - Specific subtype across all types ("*\/png")
     *
     * @param string $string Media type pattern to allow. Must contain exactly one '/'
     *        with no surrounding spaces. Examples:
     *        - Valid: "image/jpeg", "image/*", "*\/png"
     *        - Invalid: "*", "image/", "/png", "image /*", "image/ png"
     *
     * @return self For method chaining
     * @throws RuleException When:
     *         - Pattern doesn't contain '/'
     *         - Has leading/trailing spaces around '/'
     *         - Missing type or subtype part
     *         - Uses standalone "*" (use "image/*" or "*\/*" instead)
     *
     * @see MediaTypeTest::dataForAllowFailed() For all invalid cases
     */
    public function allow(string $string): MediaType
    {
        if (!str_contains($string, '/')) {
            throw new RuleException(sprintf('Media Type is wrong: %s', $string));
        }

        list($type, $subType) = $this->explode($string);

        if ($type === '*') {
            $this->allowedAllMediaType = true;
            return $this;
        }

        /** @var string[]|string $allowType */
        $allowType = $this->allowedMediaType[$type] ?? [];


        if ($allowType === '*') {
            return $this;
        }

        if ($subType === '*') {
            $allowType = $subType;
        } else {
            $allowType[] = $subType;
        }

        if (is_array($allowType)) {
            $allowType = array_unique($allowType);
        }

        $this->allowedMediaType[$type] = $allowType;
        return $this;
    }


    /**
     * Gets currently allowed media types
     *
     * @return array|string Allowed media types configuration
     */
    public function getAllowedMediaType(): array|string
    {
        return $this->allowedMediaType;
    }

    /**
     * Validates and splits media type into type/subtype components
     *
     * @param string $string Media type to validate and split (e.g. "image/jpeg")
     * @return string[] Array with exactly two elements: [type, subtype]
     * @throws RuleException When:
     *         - Input doesn't contain exactly one '/' character
     *         - Either type or subtype is empty
     *         - There are spaces around the '/' separator
     *         - The format is invalid (e.g. "image/", "/png", "image /*")
     *
     * @see MediaTypeTest::dataForAllowFailed() For all invalid format cases
     */
    private function explode(string $string): array
    {
        list($type, $subType) = explode('/', trim($string));

        if (empty($type)
            || empty($subType)
            || str_ends_with($type, ' ')
            || str_starts_with($subType, ' ')
        ) {
            throw new RuleException(sprintf('Media Type is wrong: `%s`', $string));
        }

        return array($type, $subType);
    }
}
