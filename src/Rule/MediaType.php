<?php

declare(strict_types=1);

namespace Enjoys\Upload\Rule;

use Enjoys\Upload\Exception\RuleException;
use Enjoys\Upload\RuleInterface;
use Psr\Http\Message\UploadedFileInterface;

final class MediaType implements RuleInterface
{

    private array $allowedMediaType = [];
    private string $errorMessage;

    public function __construct(string $errorMessage = null)
    {
        $this->errorMessage = $errorMessage ?? 'The Error Message Media Type';
    }

    public function check(UploadedFileInterface $file): void
    {
        $mediaType = $file->getClientMediaType();

//        if (!in_array($mediaType, $this->allowedMediaType, true)){
            throw new RuleException(sprintf($this->errorMessage, $mediaType));
//        }
    }

    public function allow(string $string)
    {
        $this->allowedMediaType[] = $string;
    }
}
