<?php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Convert base64 encoded file to actual file
 * @param string $base64File
 *
 * @return Illuminate\Http\UploadedFile
 */
function base64ToFile($base64File)
{
    // decode the base64 file
    $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64File));

    // save it to temporary dir first.
    $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
    file_put_contents($tmpFilePath, $fileData);

    // this just to help us get file info.
    $tmpFile = new File($tmpFilePath);

    $file = new UploadedFile(
        $tmpFile->getPathname(),
        $tmpFile->getFilename(),
        $tmpFile->getMimeType(),
        0,
        true// Mark it as test, since the file isn't from real HTTP POST.
    );

    return $file;
}
