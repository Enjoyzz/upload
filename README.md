Upload
==========

[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FEnjoyzz%2Fupload%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/Enjoyzz/upload/master)
[![tests](https://github.com/Enjoyzz/upload/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/Enjoyzz/upload/actions/workflows/tests.yml)
[![static](https://github.com/Enjoyzz/upload/actions/workflows/static.yml/badge.svg?branch=master)](https://github.com/Enjoyzz/upload/actions/workflows/static.yml)
[![Build Status](https://scrutinizer-ci.com/g/Enjoyzz/upload/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/upload/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Enjoyzz/upload/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/upload/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Enjoyzz/upload/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/upload/?branch=master)

File uploads library with validation
uses [PSR-7 UploadedFileInterface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md#16-uploaded-files)
and [League\Flysystem](https://github.com/thephpleague/flysystem) as a file storage library

## Installation

> Requires PHP 8.2 or later.

Install via Composer:

```shell
composer require enjoys/upload:^4.0
```

### Basic Usage

```php

use Psr\Http\Message\ServerRequestInterface;

/** @var Psr\Http\Message\UploadedFileInterface $uploadedFile */
/** @var League\Flysystem\Filesystem $filesystem */

$file = new Enjoys\Upload\UploadProcessing($uploadedFile, $filesystem);

try {
    $file->upload();       
} catch (\Exception $e) {
    // Handle exception
}
```

### Validation Rules

The library includes *3 built-in validation rules*. You can also create custom rules by implementing `Enjoys\Upload\RuleInterface`.

#### Available rules:
- Extension (Enjoys\Upload\Rule\Extension) — Validates file extensions
- Size (Enjoys\Upload\Rule\Size) — Validates file size
- MediaType (Enjoys\Upload\Rule\MediaType) — Validates MIME types

#### Adding Validation Rules

```php
/** @var Enjoys\Upload\UploadProcessing $file */
/** @var Enjoys\Upload\RuleInterface $rule */

$file->addRule($rule); // Add before calling upload()
$file->upload();
```

##### Extension Rule

Case-insensitive extension validation:

```php
$rule = new Enjoys\Upload\Rule\Extension();
$rule->allow('png'); // Single extension
$rule->allow('png, jpg'); // Comma-separated
$rule->allow(['png', 'jpg']); // Array of extensions
```

##### Size Rule

```php
$rule = new Enjoys\Upload\Rule\Size();
$rule->setMaxSize(10 * 1024 * 1024) // 10MB
     ->setMinSize(1 * 1024 * 1024); // 1MB (values in bytes)
```

##### MediaType Rule

```php
$rule = new Enjoys\Upload\Rule\MediaType();
$rule->allow('image/*') // All image types
     ->allow('application/pdf') // PDF files
     ->allow('*/vnd.openxmlformats-officedocument.*'); // Office documents
```
### Event System

The library provides PSR-14 compatible events:

#### Available Events:
- **`BeforeValidationEvent`** - Dispatched before validation starts
- **`BeforeUploadEvent`** - Dispatched after validation, before file upload
- **`AfterUploadEvent`** - Dispatched after successful file upload
- **`UploadErrorEvent`** - Dispatched when any error occurs

#### Usage Example:
```php
use Enjoys\Upload\Event\AfterUploadEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/** @var EventDispatcherInterface $dispatcher */

// Initialize with event dispatcher
$upload = new UploadProcessing($uploadedFile, $filesystem, $dispatcher);

// Add event listener
$dispatcher->addListener(
    AfterUploadEvent::class,
    function (AfterUploadEvent $event) {
        logger()->info("File uploaded to: " . $event->uploadProcessing->getTargetPath());
    }
);

$upload->upload();
```

#### Event Propagation:
All events implement `StoppableEventInterface`. To stop further processing:
```php
$dispatcher->addListener(
    BeforeUploadEvent::class,
    function (BeforeUploadEvent $event) {
        if ($shouldStop) {
            $event->stopPropagation(); // Stops other listeners
        }
    }
);
```

### API Reference

#### Enjoys\Upload\UploadProcessing::class

**setFilename(filename: string)**

Sets a new filename for the uploaded file (call before upload).

**addRule(rule: Enjoys\Upload\RuleInterface)**

Adds a single validation rule (call before upload).

**addRules(rules: Enjoys\Upload\RuleInterface[])**

Adds multiple validation rules (call before upload).

**upload(targetPath: string)**

Processes the file upload. Optionally specify a subdirectory.

**getTargetPath(): ?string**

Returns the file storage path after upload, or null if not uploaded.

**getFilesystem()**

Returns the `League\Flysystem\Filesystem::class` instance.

**getUploadedFile(): UploadedFileInterface**

Returns the PSR-7 UploadedFileInterface instance.

**getFileInfo()**

Returns the FileInfo object with file metadata.

#### Enjoys\Upload\FileInfo::class

**getFilename(): string**

Returns the full filename (e.g., new_file_name.jpg).

**getOriginalFilename(): string**

Returns the original uploaded filename.

**getFilenameWithoutExtension(): string**

Returns the filename without extension.

**getExtension(): string**

Returns the file extension (without dot).

**getExtensionWithDot(): string**

Returns the file extension with leading dot.

**getSize(): int**

Returns the file size in bytes.

**getMediaType(): string**

Returns the client-reported MIME type.
