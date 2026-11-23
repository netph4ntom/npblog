<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MediaProcessor
{
    /**
     * Maximum allowed file size in bytes (5 MB).
     */
    public const int MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Allowed MIME types strictly verified on the server.
     *
     * @var list<string>
     */
    public const array ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Maximum dimension (width or height) for the main image.
     */
    public const int MAX_DIMENSION = 1600;

    /**
     * Target widths for generated responsive thumbnails.
     *
     * @var list<int>
     */
    public const array THUMBNAIL_WIDTHS = [400, 800];

    /**
     * Process an uploaded image file:
     * 1. Validate size, filename, and server-detected MIME type.
     * 2. Strip EXIF metadata and preserve JPEG rotation.
     * 3. Proportional downscale if dimensions exceed 1600px.
     * 4. Encode as WebP and generate 400px and 800px thumbnails.
     * 5. Store files under random unique names and persist Media model.
     *
     * @throws InvalidArgumentException
     */
    public function process(
        UploadedFile $file,
        User $user,
        ?string $altText = null,
        string $disk = 'public'
    ): Media {
        // 1. Validate file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('Ukuran gambar melebihi batas maksimum 5 MB.');
        }

        // 2. Validate filename against dangerous double extensions
        $originalName = $file->getClientOriginalName();
        $this->validateFilename($originalName);

        // 3. Server-side MIME validation via fileinfo
        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath)) {
            throw new InvalidArgumentException('File gambar tidak valid atau tidak terbaca.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($realPath);

        if (! in_array($detectedMime, self::ALLOWED_MIMES, true)) {
            throw new InvalidArgumentException(
                "Format file tidak didukung: {$detectedMime}. Hanya format JPEG, PNG, dan WebP yang diizinkan."
            );
        }

        // 4. Handle EXIF orientation for JPEG before decoding
        $orientation = 1;
        if ($detectedMime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($realPath);
            if (! empty($exif['Orientation']) && is_numeric($exif['Orientation'])) {
                $orientation = (int) $exif['Orientation'];
            }
        }

        // Decode source image using GD
        $gdImage = match ($detectedMime) {
            'image/jpeg' => @imagecreatefromjpeg($realPath),
            'image/png' => @imagecreatefrompng($realPath),
            default => @imagecreatefromwebp($realPath),
        };

        if ($gdImage === false) {
            throw new InvalidArgumentException('Data gambar rusak atau tidak valid.');
        }

        // Apply orientation fix if needed
        if ($orientation === 3) {
            $rotated = imagerotate($gdImage, 180, 0);
            if ($rotated !== false) {
                imagedestroy($gdImage);
                $gdImage = $rotated;
            }
        } elseif ($orientation === 6) {
            $rotated = imagerotate($gdImage, -90, 0);
            if ($rotated !== false) {
                imagedestroy($gdImage);
                $gdImage = $rotated;
            }
        } elseif ($orientation === 8) {
            $rotated = imagerotate($gdImage, 90, 0);
            if ($rotated !== false) {
                imagedestroy($gdImage);
                $gdImage = $rotated;
            }
        }

        $origWidth = imagesx($gdImage);
        $origHeight = imagesy($gdImage);

        // 5. Downscale main image to max 1600px keeping aspect ratio
        $mainImage = $this->resizeKeepingRatio($gdImage, $origWidth, $origHeight, self::MAX_DIMENSION);
        $finalWidth = imagesx($mainImage);
        $finalHeight = imagesy($mainImage);

        // 6. Generate random unique hash
        $hash = Str::random(40);
        $mainPath = "media/{$hash}.webp";

        // 7. Save main image as WebP
        ob_start();
        imagewebp($mainImage, null, 85);
        $mainBinary = (string) ob_get_clean();

        $storage = Storage::disk($disk);
        $storage->put($mainPath, $mainBinary);
        $finalSize = strlen($mainBinary);

        // 8. Generate thumbnails (400px and 800px)
        foreach (self::THUMBNAIL_WIDTHS as $targetWidth) {
            $thumbImage = $this->resizeWidth($gdImage, $origWidth, $origHeight, $targetWidth);
            ob_start();
            imagewebp($thumbImage, null, 80);
            $thumbBinary = (string) ob_get_clean();
            imagedestroy($thumbImage);

            $thumbPath = "media/thumbnails/{$hash}-{$targetWidth}.webp";
            $storage->put($thumbPath, $thumbBinary);
        }

        // Free memory
        if ($mainImage !== $gdImage) {
            imagedestroy($mainImage);
        }
        imagedestroy($gdImage);

        // 9. Persist Media record
        return Media::create([
            'user_id' => $user->id,
            'disk' => $disk,
            'path' => $mainPath,
            'original_name' => pathinfo($originalName, PATHINFO_BASENAME),
            'mime' => 'image/webp',
            'size' => $finalSize,
            'width' => $finalWidth,
            'height' => $finalHeight,
            'alt_text' => $altText ? trim($altText) : null,
        ]);
    }

    /**
     * Validate against dangerous filenames (double extensions, executables, scripts).
     */
    protected function validateFilename(string $filename): void
    {
        $lowercase = strtolower($filename);
        $forbiddenExtensions = [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'pht', 'phar',
            'exe', 'sh', 'bash', 'bat', 'cmd', 'bin', 'js', 'html', 'htm', 'svg',
            'py', 'pl', 'cgi', 'asp', 'aspx', 'jsp',
        ];

        $parts = explode('.', $lowercase);
        if (count($parts) > 2) {
            for ($i = 1; $i < count($parts) - 1; $i++) {
                if (in_array($parts[$i], $forbiddenExtensions, true)) {
                    throw new InvalidArgumentException('Nama file mengandung ekstensi berbahaya.');
                }
            }
        }

        $ext = end($parts);
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new InvalidArgumentException('Ekstensi file tidak diizinkan. Hanya jpg, jpeg, png, webp yang didukung.');
        }
    }

    /**
     * Proportional resize if width or height exceeds maxDimension.
     */
    protected function resizeKeepingRatio(GdImage $src, int $w, int $h, int $maxDimension): GdImage
    {
        if ($w <= $maxDimension && $h <= $maxDimension) {
            $copy = imagecreatetruecolor($w, $h);
            if ($copy !== false) {
                imagealphablending($copy, false);
                imagesavealpha($copy, true);
                imagecopy($copy, $src, 0, 0, 0, 0, $w, $h);

                return $copy;
            }
        }

        $ratio = min($maxDimension / $w, $maxDimension / $h);
        $newW = (int) max(1, round($w * $ratio));
        $newH = (int) max(1, round($h * $ratio));

        $target = imagecreatetruecolor($newW, $newH);
        if ($target === false) {
            throw new InvalidArgumentException('Gagal mengalokasikan memori grafis GD.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $newW, $newH, $transparent);

        imagecopyresampled($target, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return $target;
    }

    /**
     * Resize based on target width (preserving aspect ratio).
     */
    protected function resizeWidth(GdImage $src, int $w, int $h, int $targetWidth): GdImage
    {
        if ($w <= $targetWidth) {
            $copy = imagecreatetruecolor($w, $h);
            if ($copy !== false) {
                imagealphablending($copy, false);
                imagesavealpha($copy, true);
                imagecopy($copy, $src, 0, 0, 0, 0, $w, $h);

                return $copy;
            }
        }

        $ratio = $targetWidth / $w;
        $newW = $targetWidth;
        $newH = (int) max(1, round($h * $ratio));

        $target = imagecreatetruecolor($newW, $newH);
        if ($target === false) {
            throw new InvalidArgumentException('Gagal mengalokasikan memori grafis GD.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $newW, $newH, $transparent);

        imagecopyresampled($target, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return $target;
    }
}
