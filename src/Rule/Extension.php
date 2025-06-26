<?php

declare(strict_types=1);


namespace Enjoys\Upload\Rule;


use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\RuleInterface;
use Psr\Http\Message\UploadedFileInterface;

use function array_map;
use function explode;
use function pathinfo;
use function sprintf;
use function strtolower;

final class Extension implements RuleInterface
{
    /**
     * @var string Error message template (uses %s placeholder for invalid extension)
     */
    private string $errorMessage;

    /**
     * @var string[] Array of allowed lowercase file extensions (without dots)
     */
    private array $allowed = [];

    /**
     * @param string|null $errorMessage Custom error message when validation fails.
     * The %s placeholder will be replaced with the invalid extension.
     * Default: "Files with the %s extension are not allowed"
     */
    public function __construct(string $errorMessage = null)
    {
        $this->errorMessage = $errorMessage ?? 'Files with the %s extension are not allowed';
    }


    /**
     * Adds allowed file extension(s)
     *
     * @param string|string[] $extension Allowed extension(s):
     *        - String with comma-separated values ("jpg,png,gif")
     *        - Array of extensions (["jpg", "png", "gif"])
     *        Extensions are case-insensitive (automatically converted to lowercase)
     * @return self For method chaining
     */
    public function allow(array|string $extension): Extension
    {
        if (is_string($extension)) {
            $extension = explode(",", $extension);
        }

        $this->allowed = array_map('trim', array_map('strtolower', $extension));

        return $this;
    }

    /**
     * Validates the uploaded file's extension
     *
     * @param UploadedFileInterface $file Uploaded file to validate
     * @throws RuleException When file extension is not in allowed list
     */
    #[\Override]
    public function check(UploadedFileInterface $file): void
    {
        $extension = strtolower(
            pathinfo(
                $file->getClientFilename() ?? '',
                PATHINFO_EXTENSION
            )
        );

        if (!in_array($extension, $this->allowed, true)) {
            throw new RuleException(sprintf($this->errorMessage, $extension));
        }
    }
}
