<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Trait for handling media operations
 */
trait HandlesMediaOperations
{
    /**
     * Store an uploaded image with optional resizing.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string  $directory  The storage directory
     * @param  int|null  $width  Optional width to resize to
     * @param  int|null  $height  Optional height to resize to
     * @param  bool  $keepAspectRatio  Whether to maintain aspect ratio when resizing
     * @param  int  $quality  JPEG/PNG save quality (0-100)
     * @return string The path to the stored file
     */
    protected function storeImage(
        UploadedFile $file,
        string $directory,
        ?int $width = null,
        ?int $height = null,
        bool $keepAspectRatio = true,
        int $quality = 80
    ): string {
        // Generate a unique filename
        $filename = $this->generateUniqueFilename($file);
        $fullPath = $directory.'/'.$filename;

        // Check if resizing is needed
        if ($width || $height) {
            // Load the image using Intervention Image
            $image = Image::make($file->getRealPath());

            // Resize the image
            if ($width && $height) {
                if ($keepAspectRatio) {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } else {
                    $image->resize($width, $height);
                }
            } elseif ($width) {
                $image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } elseif ($height) {
                $image->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Save the resized image
            $image->save(storage_path('app/public/'.$fullPath), $quality);

            return $fullPath;
        }

        // If no resizing needed, store the file directly
        $file->storeAs('public/'.$directory, $filename);

        return $fullPath;
    }

    /**
     * Generate a thumbnail from an image.
     *
     * @param  string  $imagePath  The path to the original image
     * @param  string  $directory  The storage directory for the thumbnail
     * @param  int  $width  Thumbnail width
     * @param  int  $height  Thumbnail height
     * @param  bool  $keepAspectRatio  Whether to maintain aspect ratio
     * @param  int  $quality  JPEG/PNG save quality (0-100)
     * @return string The path to the thumbnail
     */
    protected function generateThumbnail(
        string $imagePath,
        string $directory,
        int $width = 200,
        int $height = 200,
        bool $keepAspectRatio = true,
        int $quality = 70
    ): string {
        // Get the original image from storage
        $originalPath = storage_path('app/public/'.$imagePath);

        // Generate thumbnail filename
        $pathInfo = pathinfo($imagePath);
        $thumbnailFilename = $pathInfo['filename'].'_thumb.'.$pathInfo['extension'];
        $thumbnailPath = $directory.'/'.$thumbnailFilename;

        // Create the thumbnail using Intervention Image
        $image = Image::make($originalPath);

        if ($keepAspectRatio) {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } else {
            $image->fit($width, $height);
        }

        // Save the thumbnail
        $image->save(storage_path('app/public/'.$thumbnailPath), $quality);

        return $thumbnailPath;
    }

    /**
     * Store an uploaded file (non-image).
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string  $directory  The storage directory
     * @return string The path to the stored file
     */
    protected function storeFile(UploadedFile $file, string $directory): string
    {
        // Generate a unique filename
        $filename = $this->generateUniqueFilename($file);

        // Store the file
        $file->storeAs('public/'.$directory, $filename);

        return $directory.'/'.$filename;
    }

    /**
     * Generate a unique filename for an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @return string The unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExtension = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));

        // Generate a unique name with timestamp and random string
        $uniqueName = $nameWithoutExtension.'_'.time().'_'.Str::random(10).'.'.$extension;

        return $uniqueName;
    }

    /**
     * Delete a file from storage.
     *
     * @param  string  $path  The path to the file
     * @return bool Whether the deletion was successful
     */
    protected function deleteFile(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * Delete a file and its thumbnail.
     *
     * @param  string  $path  The path to the file
     * @return bool Whether the deletion was successful
     */
    protected function deleteFileWithThumbnail(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $result = $this->deleteFile($path);

        // Try to delete the thumbnail if it exists
        $pathInfo = pathinfo($path);
        $thumbnailPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_thumb.'.$pathInfo['extension'];

        $this->deleteFile($thumbnailPath);

        return $result;
    }

    /**
     * Get the file validation rules based on file type.
     *
     * @param  string  $type  The file type (image, document, video, audio)
     * @param  int  $maxSize  Maximum file size in kilobytes
     * @return array The validation rules
     */
    protected function getFileValidationRules(string $type = 'image', int $maxSize = 5120): array
    {
        $rules = [
            'required',
            'file',
            'max:'.$maxSize,
        ];

        switch ($type) {
            case 'image':
                $rules[] = 'mimes:jpeg,png,jpg,gif,webp';
                $rules[] = 'image';
                break;

            case 'document':
                $rules[] = 'mimes:pdf,doc,docx,txt,rtf,odt,xls,xlsx,csv,ppt,pptx';
                break;

            case 'video':
                $rules[] = 'mimes:mp4,avi,mov,wmv,flv,mkv,webm';
                break;

            case 'audio':
                $rules[] = 'mimes:mp3,wav,ogg,aac,flac';
                break;

            case 'gedcom':
                $rules[] = 'mimes:ged,gedcom,txt';
                break;

            default:
                // Allow any file type for 'any' type
                break;
        }

        return $rules;
    }

    /**
     * Process an uploaded image from a request.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $fieldName  The form field name
     * @param  string  $directory  The storage directory
     * @param  int|null  $width  Optional width to resize to
     * @param  int|null  $height  Optional height to resize to
     * @param  bool  $createThumbnail  Whether to create a thumbnail
     * @return string|null The path to the stored image or null if no image was uploaded
     */
    protected function processUploadedImage(
        Request $request,
        string $fieldName,
        string $directory,
        ?int $width = null,
        ?int $height = null,
        bool $createThumbnail = false
    ): ?string {
        if (! $request->hasFile($fieldName) || ! $request->file($fieldName)->isValid()) {
            return null;
        }

        $file = $request->file($fieldName);
        $path = $this->storeImage($file, $directory, $width, $height);

        if ($createThumbnail) {
            $this->generateThumbnail($path, $directory);
        }

        return $path;
    }

    /**
     * Process an uploaded file from a request.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $fieldName  The form field name
     * @param  string  $directory  The storage directory
     * @return string|null The path to the stored file or null if no file was uploaded
     */
    protected function processUploadedFile(
        Request $request,
        string $fieldName,
        string $directory
    ): ?string {
        if (! $request->hasFile($fieldName) || ! $request->file($fieldName)->isValid()) {
            return null;
        }

        $file = $request->file($fieldName);

        return $this->storeFile($file, $directory);
    }

    /**
     * Get the public URL for a stored file.
     *
     * @param  string|null  $path  The file path
     * @return string|null The public URL or null if path is empty
     */
    protected function getFileUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Get the file extension from a path.
     *
     * @param  string  $path  The file path
     * @return string The file extension
     */
    protected function getFileExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Check if a file exists in storage.
     *
     * @param  string  $path  The file path
     * @return bool Whether the file exists
     */
    protected function fileExists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * Get the file size in bytes.
     *
     * @param  string  $path  The file path
     * @return int The file size in bytes
     */
    protected function getFileSize(string $path): int
    {
        if (! $this->fileExists($path)) {
            return 0;
        }

        return Storage::disk('public')->size($path);
    }

    /**
     * Get the file MIME type.
     *
     * @param  string  $path  The file path
     * @return string|null The MIME type or null if file doesn't exist
     */
    protected function getFileMimeType(string $path): ?string
    {
        if (! $this->fileExists($path)) {
            return null;
        }

        return Storage::disk('public')->mimeType($path);
    }

    /**
     * Get the file last modified timestamp.
     *
     * @param  string  $path  The file path
     * @return int|null The last modified timestamp or null if file doesn't exist
     */
    protected function getFileLastModified(string $path): ?int
    {
        if (! $this->fileExists($path)) {
            return null;
        }

        return Storage::disk('public')->lastModified($path);
    }

    /**
     * Get image dimensions.
     *
     * @param  string  $path  The image path
     * @return array|null Array with width and height or null if not an image
     */
    protected function getImageDimensions(string $path): ?array
    {
        if (! $this->fileExists($path)) {
            return null;
        }

        $mimeType = $this->getFileMimeType($path);

        if (! $mimeType || ! Str::startsWith($mimeType, 'image/')) {
            return null;
        }

        try {
            $image = Image::make(Storage::disk('public')->path($path));

            return [
                'width' => $image->width(),
                'height' => $image->height(),
            ];
        } catch (Exception $e) {
            return null;
        }
    }
}
