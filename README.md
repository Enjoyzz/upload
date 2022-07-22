# Example

```php

use Psr\Http\Message\ServerRequestInterface;

/** @var \Psr\Http\Message\UploadedFileInterface $uploadedFile */
/** @var \League\Flysystem\Filesystem $filesystem */

$file = new \Enjoys\Upload\UploadProcessing($uploadedFile, $filesystem);
$file->setFilename('myFile');
try {
    $file->upload();       
}catch (\Exception $e){
    // handle exception
}
```

```php
// Public methods
$file->getTargetPath(); // before upload null, after string
$file->getFilesystem(); // return object \League\Flysystem\Filesystem
$file->getUploadedFile(); // return object \Psr\Http\Message\UploadedFileInterface

$fileInfo = $file->getFileInfo(); // return object \Enjoys\Upload\FileInfo 
$fileInfo->getExtension(); // return extension, ex.  `jpg`
$fileInfo->getFilename(); // return full file name, ex.  `new_file_name.jpg`
$fileInfo->getOriginalFilename(); // return original file name, ex.  `original_file_name.jpg`
$fileInfo->getMediaType(); // return media type, determine by client extension, ex.  `image/jpg`
$fileInfo->getSize(); // return file size in bytes, ex.  `102435`
$fileInfo->getExtensionWithDot(); // return extension with dot before, ex.  `.jpg`
$fileInfo->getFilenameWithoutExtension(); // return file name without extension, ex.  `new_file_name`
```

# Validation

```php
use Psr\Http\Message\ServerRequestInterface;

/** @var \Psr\Http\Message\UploadedFileInterface $uploadedFile */
/** @var \League\Flysystem\Filesystem $filesystem */

$file = new \Enjoys\Upload\UploadProcessing($uploadedFile, $filesystem);


/** @var \Enjoys\Upload\RuleInterface $rule */
$file->addRule($rule);

// or
/** @var \Enjoys\Upload\RuleInterface[] $rules */
$file->addRules($rules);

try {
    $file->upload();       
}catch (\Exception $e){
    // handle exception
}
```

## Extension Rule

Allowed extension case-insensitive

```php
$rule = new \Enjoys\Upload\Rule\Extension();
$rule->allow('png');
// or
$rule->allow('png, jpg');
// or
$rule->allow(['png','jpg']);
```

## Size Rule

```php
$rule = new \Enjoys\Upload\Rule\Size();
$rule->setMaxSize(10*1024*1024); // in bytes
$rule->setMinSize(1*1024*1024); // in bytes
```
