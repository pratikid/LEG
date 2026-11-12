<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Trait for handling file uploads
 */
trait HandlesFileUploads
{
    /**
     * Store an uploaded file and return its path.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string  $directory  The directory to store the file in
     * @param  string|null  $filename  Custom filename (without extension)
     * @param  string  $disk  Storage disk to use
     * @return string The path to the stored file
     */
    protected function storeUploadedFile(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        string $disk = 'public'
    ): string {
        // Generate a unique filename if not provided
        if ($filename === null) {
            $filename = Str::uuid()->toString();
        }

        // Add the original file extension
        $extension = $file->getClientOriginalExtension();
        $fullFilename = $filename.'.'.$extension;

        // Store the file
        $path = $file->storeAs($directory, $fullFilename, $disk);

        if ($path === false) {
            throw new RuntimeException('Failed to store uploaded file');
        }

        return $path;
    }

    /**
     * Delete a previously uploaded file.
     *
     * @param  string  $path  The path to the file
     * @param  string  $disk  Storage disk to use
     * @return bool Whether the file was deleted
     */
    protected function deleteUploadedFile(string $path, string $disk = 'public'): bool
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk($disk)->delete($path);
    }

    /**
     * Get the public URL for a stored file.
     *
     * @param  string  $path  The path to the file
     * @param  string  $disk  Storage disk to use
     * @return string|null The public URL or null if the file doesn't exist
     */
    protected function getFileUrl(string $path, string $disk = 'public'): ?string
    {
        if (empty($path) || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        return asset(Storage::disk($disk)->path($path));
    }

    /**
     * Validate an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  array  $allowedTypes  Allowed MIME types
     * @param  int  $maxSize  Maximum file size in kilobytes
     * @return bool Whether the file is valid
     */
    protected function validateUploadedFile(
        UploadedFile $file,
        array $allowedTypes,
        int $maxSize = 10240 // 10MB default
    ): bool {
        // Check if the file was uploaded successfully
        if (! $file->isValid()) {
            return false;
        }

        // Check file size (convert KB to bytes)
        if ($file->getSize() > $maxSize * 1024) {
            return false;
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (! in_array($mimeType, $allowedTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Get common validation rules for file uploads.
     *
     * @param  string  $fileType  Type of file ('image', 'document', 'media', 'gedcom')
     * @param  int  $maxSize  Maximum file size in kilobytes
     * @return array Validation rules
     */
    protected function getFileValidationRules(string $fileType, int $maxSize = 10240): array
    {
        $rules = [
            'required',
            'file',
            'max:'.$maxSize,
        ];

        switch ($fileType) {
            case 'image':
                $rules[] = 'mimes:jpeg,png,jpg,gif';
                break;

            case 'document':
                $rules[] = 'mimes:pdf,doc,docx,txt,rtf';
                break;

            case 'media':
                $rules[] = 'mimes:mp3,mp4,wav,avi,mov';
                break;

            case 'gedcom':
                $rules[] = 'mimes:ged,txt';
                break;

            default:
                // No specific MIME type restrictions
                break;
        }

        return $rules;
    }

    /**
     * Process an image upload with optional resizing.
     *
     * @param  UploadedFile  $file  The uploaded image
     * @param  string  $directory  The directory to store the image in
     * @param  int|null  $maxWidth  Maximum width (null for no resizing)
     * @param  int|null  $maxHeight  Maximum height (null for no resizing)
     * @param  string|null  $filename  Custom filename (without extension)
     * @param  string  $disk  Storage disk to use
     * @return string The path to the stored image
     */
    protected function processImageUpload(
        UploadedFile $file,
        string $directory,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        ?string $filename = null,
        string $disk = 'public'
    ): string {
        // If no resizing needed, use the standard upload method
        if ($maxWidth === null && $maxHeight === null) {
            return $this->storeUploadedFile($file, $directory, $filename, $disk);
        }

        // Generate a unique filename if not provided
        if ($filename === null) {
            $filename = Str::uuid()->toString();
        }

        // Add the original file extension
        $extension = $file->getClientOriginalExtension();
        $fullFilename = $filename.'.'.$extension;

        // Create an image instance
        // $image = \Intervention\Image\ImageManagerStatic::make($file);
        $image = \Intervention\Image\Facades\Image::make($file);

        // Resize the image while maintaining aspect ratio
        if ($maxWidth !== null && $maxHeight !== null) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } elseif ($maxWidth !== null) {
            $image->resize($maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } elseif ($maxHeight !== null) {
            $image->resize(null, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Save the image to storage
        $path = $directory.'/'.$fullFilename;
        Storage::disk($disk)->put($path, (string) $image->encode());

        return $path;
    }
}
