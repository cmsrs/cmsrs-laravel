<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Helpers;

use App\Models\Cmsrs\Image;
use GdImage;
use RuntimeException;

class ImageHelperService
{
    /**
     * Save original image and all configured thumbnails.
     */
    public static function saveImageAndThumbs(
        string $data,
        string $dirImg,
        string $name
    ): void {
        $source = self::createImageFromString($data);

        // Save original image.
        self::saveImage($source, $dirImg.'/'.$name);

        $fileName = pathinfo($name, PATHINFO_FILENAME);
        $fileExt = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        foreach (Image::$thumbs as $thumbName => $dimension) {
            $fileThumb = $dirImg
                .'/'
                .$fileName
                .'-'
                .$thumbName
                .'.'
                .$fileExt;

            $thumbnail = self::createThumbnail(
                $source,
                $dimension['x'],
                $dimension['y']
            );

            self::saveImage($thumbnail, $fileThumb);
        }
    }

    /**
     * Create GD image from binary image data or base64 data URI.
     */
    private static function createImageFromString(string $data): GdImage
    {
        if ($data === '') {
            throw new RuntimeException('Unable to read image data: empty data.');
        }

        // Handle base64 data URI, e.g.
        // data:image/jpeg;base64,/9j/4AAQ...
        if (str_starts_with($data, 'data:')) {
            $parts = explode(',', $data, 2);

            if (count($parts) !== 2) {
                throw new RuntimeException('Unable to read image data: invalid data URI.');
            }

            $data = base64_decode($parts[1], true);

            if ($data === false) {
                throw new RuntimeException('Unable to read image data: invalid base64 data.');
            }
        }

        $image = @imagecreatefromstring($data);

        if ($image === false) {
            throw new RuntimeException('Unable to read image data.');
        }

        return $image;
    }

    /**
     * Create an exact-size thumbnail using a centered crop.
     *
     * Example:
     * 2000x1000 -> 300x300
     * The image is cropped to a centered 1000x1000 square
     * and then resized to 300x300.
     */
    private static function createThumbnail(
        GdImage $source,
        int $width,
        int $height
    ): GdImage {
        if ($width < 1 || $height < 1) {
            throw new RuntimeException('Thumbnail dimensions must be greater than zero.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $width / $height;

        if ($sourceRatio > $targetRatio) {
            // Source image is wider than target.
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);

            $sourceX = (int) round(($sourceWidth - $cropWidth) / 2);
            $sourceY = 0;
        } else {
            // Source image is taller than target.
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);

            $sourceX = 0;
            $sourceY = (int) round(($sourceHeight - $cropHeight) / 2);
        }

        $thumbnail = imagecreatetruecolor($width, $height);

        if ($thumbnail === false) {
            throw new RuntimeException('Unable to create thumbnail.');
        }

        self::prepareTransparency($thumbnail); // jpeg does not support transparency, but png and webp do

        imagecopyresampled(
            $thumbnail,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $width,
            $height,
            $cropWidth,
            $cropHeight
        );

        return $thumbnail;
    }

    /**
     * Save GD image according to the file extension.
     */
    private static function saveImage(
        GdImage $image,
        string $path
    ): void {
        $extension = strtolower(
            pathinfo($path, PATHINFO_EXTENSION)
        );

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, $path, 90);
                break;

            case 'png':
                if (! self::savePng($image, $path)) {
                    throw new RuntimeException(
                        sprintf('Unable to save image: "%s".', $path)
                    );
                }
                break;

            case 'webp':
                if (! self::saveWebP($image, $path, 90)) {
                    throw new RuntimeException(
                        sprintf('Unable to save image: "%s".', $path)
                    );
                }
                break;

            default:
                throw new RuntimeException(
                    sprintf(
                        'Unsupported image format: "%s". Supported formats: jpg, jpeg, png, webp.',
                        $extension
                    )
                );
        }
    }

    private static function prepareTransparency(
        GdImage $image
    ): void {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    /**
     * Save PNG with preserved transparency.
     */
    private static function savePng(
        GdImage $image,
        string $path
    ): bool {
        self::prepareTransparency($image);

        return imagepng($image, $path, 6);
    }

    /**
     * Save WebP with preserved transparency.
     */
    private static function saveWebP(
        GdImage $image,
        string $path,
        int $quality = 90
    ): bool {
        self::prepareTransparency($image);

        return imagewebp($image, $path, $quality);
    }
}
