<?php

declare(strict_types=1);


namespace Enjoys\Upload;


use Enjoys\Upload\Exception\RuleException;
use Psr\Http\Message\UploadedFileInterface;

interface RuleInterface
{
    /**
     * If the check failed, you need to throw an exception \Upload\Exception\Rule,
     * if successful, return nothing (void)
     *
     * @param UploadedFileInterface $file
     * @return void
     * @throws RuleException
     */
    public function check(UploadedFileInterface $file): void;
}
