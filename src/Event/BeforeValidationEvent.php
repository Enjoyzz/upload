<?php

declare(strict_types=1);

namespace Enjoys\Upload\Event;

use Enjoys\Upload\UploadProcessing;

final class BeforeValidationEvent extends AbstractUploadEvent
{
    public function __construct(public readonly UploadProcessing $uploadProcessing)
    {
    }
}
