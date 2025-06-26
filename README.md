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

### Installing

> This package requires PHP version 8.2 or later.

This package is available via Composer:

```shell
composer require enjoys/upload:^3.0
```

### Usage

```php

use Psr\Http\Message\ServerRequestInterface;

/** @var Psr\Http\Message\UploadedFileInterface $uploadedFile */
/** @var League\Flysystem\Filesystem $filesystem */

$file = new Enjoys\Upload\UploadProcessing($uploadedFile, $filesystem);
try {
    $file->upload();       
}catch (\Exception $e){
    // handle exception
}
```

### Validation

Currently there are *3 validation rules*, but if a specific validation is needed, you can write a rule by implementing the Enjoys\Upload\RuleInterface interface:

- Extension (Enjoys\Upload\Rule\Extension)
- Size (Enjoys\Upload\Rule\Size)
- MediaType (Enjoys\Upload\Rule\MediaType)

```php
/** @var Enjoys\Upload\UploadProcessing $file */
/** @var Enjoys\Upload\RuleInterface $rule */

// ... set rule before called $file->upload()
$file->addRule($rule);
$file->upload(); 
```

#### Extension Rule

Allowed extension case-insensitive

```php
$rule = new Enjoys\Upload\Rule\Extension();
$rule->allow('png');
// or
$rule->allow('png, jpg');
// or
$rule->allow(['png','jpg']);
```

#### Size Rule

```php
$rule = new Enjoys\Upload\Rule\Size();
$rule->setMaxSize(10*1024*1024)
->setMinSize(1*1024*1024); // in bytes
```

#### MediaType Rule

```php
$rule = new Enjoys\Upload\Rule\MediaType();
$rule->allow('image/*')
     ->allow('application/pdf')
     // ...
    ;
```

### Methods

#### Enjoys\Upload\UploadProcessing::class

**setFilename(filename: string)**

Set new filename for uploaded file. _Called before upload._

```php
/** @var Enjoys\Upload\UploadProcessing $file */
$file->setFilename('name');
```

**addRule(rule: Enjoys\Upload\RuleInterface)**

_Called before upload._

```php
/** @var Enjoys\Upload\UploadProcessing $file */
/** @var Enjoys\Upload\RuleInterface $rule */
$file->addRule($rule);
```

**addRules(rules: Enjoys\Upload\RuleInterface[])**

_Called before upload._

```php
/** @var Enjoys\Upload\UploadProcessing $file */
/** @var Enjoys\Upload\RuleInterface[] $rules */
$file->addRules($rules);
```

**upload(targetPath: string)**

```php
/** @var Enjoys\Upload\UploadProcessing $file */
$file->upload(); // $file->upload('sub_directory');
```

**getTargetPath()**

_Called after upload_. Something like a location in the file system is returned. If called before upload, returns `null`
.

```php
/** @var Enjoys\Upload\UploadProcessing $file */
$file->getTargetPath(); // return null or string
```

**getFilesystem()**

Returns `League\Flysystem\Filesystem::class`

```php
/** @var Enjoys\Upload\UploadProcessing $file */
$file->getFilesystem(); 
```

**getUploadedFile()**

Returns `Psr\Http\Message\UploadedFileInterface::class`

```php
/** @var Enjoys\Upload\UploadProcessing $file */
$file->getUploadedFile();
```

**getFileInfo()**

Returns `Enjoys\Upload\FileInfo::class`

```php
/** @var Enjoys\Upload\UploadProcessing $file */
$file->getFileInfo();
```

#### Enjoys\Upload\FileInfo::class

**getFilename()**

Returns full filename, ex.  `new_file_name.jpg`

```php
/** @var Enjoys\Upload\FileInfo $fileInfo */
$fileInfo->getFilename();
```

**getOriginalFilename()**

Returns original filename, ex.  `original_file_name.jpg`

```php
/** @var Enjoys\Upload\FileInfo $fileInfo */
$fileInfo->getOriginalFilename();
```

**getFilenameWithoutExtension()**

Returns filename without extension, ex.  `new_file_name`

```php
/** @var Enjoys\Upload\FileInfo $fileInfo */
$fileInfo->getFilenameWithoutExtension();
```

**getExtension()**

Returns extension, ex.  `jpg`

```php
/** @var Enjoys\Upload\FileInfo $fileInfo */
$fileInfo->getExtension();
```

**getExtensionWithDot()**

Returns extension with dot before, ex.  `.jpg`

```php
/** @var Enjoys\Upload\FileInfo $fileInfo */
$fileInfo->getExtensionWithDot();
```

**getSize()**

Returns filesize in bytes, ex.  `102435`

```php
/** @var Enjoys\Upload\FileInfo $fileInfo */
$fileInfo->getSize();
```

**getMediaType()**

Returns media type, determine by client extension, ex.  `image/jpg`

```php
/** @var Enjoys\Upload\FileInfo $fileInfo */
$fileInfo->getMediaType();
```
