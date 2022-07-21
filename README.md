# Example

```php

use Psr\Http\Message\ServerRequestInterface;

/** @var \Psr\Http\Message\UploadedFileInterface $uploadedFile */
/** @var \League\Flysystem\Filesystem $filesystem */

$file = new \Enjoys\Upload\UploadProcessing($uploadedFile, $storage);
$file->setFilename('new_file_name');
try {
    $file->upload();       
}catch (\Exception $e){
    // handle exception
}

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
