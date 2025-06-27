<?php

declare(strict_types=1);


namespace Enjoys\Upload\Event;


use Enjoys\Upload\UploadProcessing;

final class UploadErrorEvent extends AbstractUploadEvent
{
    public function __construct(
        public readonly UploadProcessing $file,
        public readonly \Throwable $exception
    ) {
    }
}
