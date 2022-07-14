<?php

declare(strict_types=1);

namespace Enjoys\Upload;

interface StorageInterface
{
    public function upload(File $file): ?string;
}
