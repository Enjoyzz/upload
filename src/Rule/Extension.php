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
    private string $errorMessage;
    private array $allowed = [];

    public function __construct(string $errorMessage = null)
    {
        $this->errorMessage = $errorMessage ?? 'Files with the %s extension are not allowed';
    }


    /**
     * @param string|string[] $extension
     * @return $this
     */
    public function allow(array|string $extension): Extension
    {
        if (is_string($extension)){
            $extension = explode(",", $extension);
        }

        $this->allowed = array_map('trim', array_map('strtolower', $extension));

        return $this;
    }

    public function check(UploadedFileInterface $file): void
    {
        $extension = strtolower(pathinfo(
            $file->getClientFilename() ?? '',
            PATHINFO_EXTENSION
        ));

        if (!in_array($extension, $this->allowed, true)){
            throw new RuleException(sprintf($this->errorMessage, $extension));
        }
    }
}
