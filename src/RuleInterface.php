<?php

declare(strict_types=1);


namespace Enjoys\Upload;


use Psr\Http\Message\UploadedFileInterface;

interface RuleInterface
{
    public function check(UploadedFileInterface $file): void;
}
