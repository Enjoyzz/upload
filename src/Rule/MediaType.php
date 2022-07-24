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
    private bool $allowedAllMediaType = false;

    public function __construct(string $errorMessage = null)
    {
        $this->errorMessage = $errorMessage ?? 'Media type is disallow: `%s`';
    }

    public function check(UploadedFileInterface $file): void
    {
        $mediaType = $file->getClientMediaType() ?? throw new RuleException('Media Type ins null');

        if ($this->allowedAllMediaType) {
            return;
        }

        list($type, $subType) = $this->explode($mediaType);

        if (!array_key_exists($type, $this->allowedMediaType)){
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


    public function getAllowedMediaType(): array|string
    {
        return $this->allowedMediaType;
    }

    /**
     * @param string $string
     * @return string[]
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
