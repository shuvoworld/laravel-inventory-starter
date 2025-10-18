<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class ImageService
{
    /**
     * Upload and optimize a product image.
     *
     * @param UploadedFile $file
     * @param string|null $oldImagePath
     * @return string The path to the stored image
     */
    public function uploadProductImage(UploadedFile $file, ?string $oldImagePath = null): string
    {
        // Delete old image if exists
        if ($oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = 'products/' . $filename;

        // Store the file
        $file->storeAs('products', $filename, 'public');

        // Optimize the image if GD or Imagick is available
        $this->optimizeImage($path);

        return $path;
    }

    /**
     * Optimize an image (resize and compress).
     *
     * @param string $path Path relative to storage/app/public
     * @return void
     */
    protected function optimizeImage(string $path): void
    {
        try {
            $fullPath = storage_path('app/public/' . $path);

            if (!file_exists($fullPath)) {
                return;
            }

            // Check if GD is available
            if (!extension_loaded('gd')) {
                return;
            }

            // Get image info
            $imageInfo = getimagesize($fullPath);
            if (!$imageInfo) {
                return;
            }

            $mimeType = $imageInfo['mime'];
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Max dimensions
            $maxWidth = 800;
            $maxHeight = 800;

            // Skip if image is already small enough
            if ($width <= $maxWidth && $height <= $maxHeight) {
                return;
            }

            // Calculate new dimensions maintaining aspect ratio
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            // Create image resource based on type
            switch ($mimeType) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($fullPath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($fullPath);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($fullPath);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($fullPath);
                    break;
                default:
                    return;
            }

            if (!$source) {
                return;
            }

            // Create new image
            $destination = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG and GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Resize
            imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save optimized image
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($destination, $fullPath, 85);
                    break;
                case 'image/png':
                    imagepng($destination, $fullPath, 8);
                    break;
                case 'image/gif':
                    imagegif($destination, $fullPath);
                    break;
                case 'image/webp':
                    imagewebp($destination, $fullPath, 85);
                    break;
            }

            // Free memory
            imagedestroy($source);
            imagedestroy($destination);

        } catch (\Exception $e) {
            // Log error but don't fail - image is already uploaded
            \Log::warning('Image optimization failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a product image.
     *
     * @param string|null $imagePath
     * @return bool
     */
    public function deleteProductImage(?string $imagePath): bool
    {
        if (!$imagePath) {
            return false;
        }

        return Storage::disk('public')->delete($imagePath);
    }

    /**
     * Get optimized image URL.
     *
     * @param string|null $imagePath
     * @return string|null
     */
    public function getImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        return asset('storage/' . $imagePath);
    }
}
