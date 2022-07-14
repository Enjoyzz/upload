# Example

```injectablephp
use Enjoys\Upload\File;
use Enjoys\Upload\Storage\FileSystem;
use Psr\Http\Message\ServerRequestInterface;

/** @var ServerRequestInterface $request */
$uploadedFile = $request->getUploadedFiles()['my_file'];
$storage = new FileSystem('/tmp/upload');
$file = new File($uploadedFile, $storage);
$file->setFilename('new_file_name');
try {
    $targetPath = $file->upload();
    /*
     * $targetPath - full path to uploaded file
     * $file->getFilename(); - return full file name, ex.  `new_file_name.jpg`
     * $file->getFilenameWithoutExtension(); - return file name without extension, ex.  `new_file_name`
     * $file->getExtension(); - return extension, ex.  `jpg`
     * $file->getExtensionWithDot(); - return extension with dot before, ex.  `.jpg`
     * $file->getSize(); - return file size in bytes, ex.  `102435`
     * $file->getOriginalFilename(); - return original file name, ex.  `original_file_name.jpg`
     * $file->getMimeType(); - return mime type, determine by client extension, ex.  `image/jpg`
     * $file->getUploadedFile(); - return object `\Psr\Http\Message\UploadedFileInterface`
     */
}catch (\Exception $e){
    // handle exception
}
```
