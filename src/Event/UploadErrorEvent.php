<?php

declare(strict_types=1);


namespace Enjoys\Upload\Event;


use Enjoys\Upload\UploadProcessing;

final class UploadErrorEvent extends AbstractUploadEvent
{
    public function __construct(
        public readonly UploadProcessing $uploadProcessing,
        public readonly \Throwable $exception
    ) {
    }
}
