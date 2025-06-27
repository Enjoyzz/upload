<?php

declare(strict_types=1);

namespace Enjoys\Upload\Event;

use Enjoys\Upload\Event\AbstractUploadEvent;
use Enjoys\Upload\UploadProcessing;
use Psr\Http\Message\UploadedFileInterface;

final class BeforeValidationEvent extends AbstractUploadEvent
{
    public function __construct(public readonly UploadProcessing $file)
    {
    }
}
